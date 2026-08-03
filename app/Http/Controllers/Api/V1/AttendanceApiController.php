<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckInRequest;
use App\Http\Requests\Api\V1\CheckOutRequest;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Models\Attendance;
use App\Services\ApprovalFlowService;
use App\Services\FaceRecognitionService;
use App\Services\FileNamingService;
use App\Services\GeofenceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AttendanceApiController extends Controller
{
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;
        $intern = $user->intern;
        $freelancer = $user->freelancer;

        $profile = $employee ?? $intern ?? $freelancer;

        if (! $profile) {
            return response()->json(['message' => 'Profile absensi tidak ditemukan.'], 422);
        }

        $todayAttendance = Attendance::byWorker($user)->whereDate('date', today())->first();

        if ($todayAttendance && $todayAttendance->check_in) {
            return response()->json(['message' => 'Sudah melakukan check in hari ini.'], 409);
        }

        $company = $profile->company;
        $policy = $company?->policy;

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        $isOutOfBounds = false;
        if ($policy?->require_gps) {
            if (! $lat || ! $lng) {
                return response()->json(['message' => 'Lokasi GPS wajib diisi.'], 422);
            }

            if ($company) {
                $geofenceResult = GeofenceService::validateCompanyGeofence($company, (float) $lat, (float) $lng);

                if (! $geofenceResult['is_valid']) {
                    $isOutOfBounds = true;
                }
            }
        }

        if ($policy?->require_photo && ! $request->hasFile('photo')) {
            return response()->json(['message' => 'Foto selfie absensi wajib diunggah.'], 422);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $identifier = $profile->employee_code ?? $profile->nik ?? (string) ($profile->id ?? $user->id);
            $photoPath = FileNamingService::storeUploadedFile(
                $request->file('photo'),
                'attendance/photos',
                'ATT_IN',
                $identifier,
                config('filesystems.default')
            );
        }

        $isFaceVerified = null;
        $faceMatchScore = null;
        $faceVerificationNotes = null;
        $faceFailedAndRequiresApproval = false;

        if ($policy?->require_face_recognition) {
            $faceResult = app(FaceRecognitionService::class)->verifyFace(
                $photoPath,
                $profile->master_face_photo ?? null,
                $policy
            );

            $isFaceVerified = $faceResult['is_matched'];
            $faceMatchScore = $faceResult['score'];
            $faceVerificationNotes = $faceResult['notes'];

            if (! $isFaceVerified) {
                if ($policy->face_fail_action === 'reject') {
                    return response()->json([
                        'message' => 'Verifikasi Face Recognition Gagal: '.$faceVerificationNotes,
                    ], 422);
                } else {
                    $faceFailedAndRequiresApproval = true;
                }
            }
        }

        $now = now();
        $workStartStr = $policy?->work_start_time ?? '08:00:00';
        $workStart = Carbon::parse($todayAttendance?->date ?? today()->toDateString().' '.$workStartStr);
        $toleranceMinutes = $policy?->late_tolerance_minutes ?? 15;
        $lateThreshold = (clone $workStart)->addMinutes($toleranceMinutes);

        $requiresApproval = $isOutOfBounds || $faceFailedAndRequiresApproval;
        $status = $requiresApproval ? AttendanceStatus::PendingApproval : AttendanceStatus::OnTime;
        $lateMinutes = 0;

        if (! $requiresApproval && $now->greaterThan($lateThreshold)) {
            $status = AttendanceStatus::Late;
            $lateMinutes = (int) $workStart->diffInMinutes($now);
        }

        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $employee?->id,
                'intern_id' => $intern?->id,
                'freelancer_id' => $freelancer?->id,
                'date' => today()->toDateString(),
            ],
            [
                'check_in' => $now->toTimeString(),
                'check_in_lat' => $lat,
                'check_in_lng' => $lng,
                'check_in_photo' => $photoPath,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'is_out_of_bounds' => $isOutOfBounds,
                'is_face_verified' => $isFaceVerified,
                'face_match_score' => $faceMatchScore,
                'face_verification_notes' => $faceVerificationNotes,
            ]
        );

        if ($requiresApproval) {
            app(ApprovalFlowService::class)->generateSteps($attendance, 'geofence');
        }

        return response()->json([
            'message' => $requiresApproval
                ? 'Check in berhasil dicatat dan sedang menunggu persetujuan atasan.'
                : 'Check in berhasil',
            'attendance' => new AttendanceResource($attendance),
        ]);
    }

    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;
        $intern = $user->intern;
        $freelancer = $user->freelancer;

        $profile = $employee ?? $intern ?? $freelancer;

        if (! $profile) {
            return response()->json(['message' => 'Profile absensi tidak ditemukan.'], 422);
        }

        $attendance = Attendance::byWorker($user)->whereDate('date', today())->first();

        if (! $attendance || ! $attendance->check_in) {
            return response()->json(['message' => 'Anda belum melakukan check in hari ini.'], 422);
        }

        if ($attendance->check_out) {
            return response()->json(['message' => 'Sudah melakukan check out hari ini.'], 409);
        }

        $company = $profile->company;
        $policy = $company?->policy;

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        $isOutOfBounds = false;
        if ($policy?->require_gps) {
            if (! $lat || ! $lng) {
                return response()->json(['message' => 'Lokasi GPS wajib diisi.'], 422);
            }

            if ($company) {
                $geofenceResult = GeofenceService::validateCompanyGeofence($company, (float) $lat, (float) $lng);

                if (! $geofenceResult['is_valid']) {
                    $isOutOfBounds = true;
                }
            }
        }

        if ($policy?->require_photo && ! $request->hasFile('photo')) {
            return response()->json(['message' => 'Foto selfie absensi wajib diunggah.'], 422);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $identifier = $profile->employee_code ?? $profile->nik ?? (string) ($profile->id ?? $user->id);
            $photoPath = FileNamingService::storeUploadedFile(
                $request->file('photo'),
                'attendance/photos',
                'ATT_OUT',
                $identifier,
                config('filesystems.default')
            );
        }

        $updateData = [
            'check_out' => now()->toTimeString(),
            'check_out_lat' => $lat,
            'check_out_lng' => $lng,
            'check_out_photo' => $photoPath,
        ];

        if ($isOutOfBounds) {
            $updateData['is_out_of_bounds'] = true;
            $updateData['status'] = AttendanceStatus::PendingApproval;
        }

        $attendance->update($updateData);

        if ($isOutOfBounds) {
            if ($attendance->approvalSteps()->count() === 0) {
                app(ApprovalFlowService::class)->generateSteps($attendance, 'geofence');
            }
        }

        return response()->json([
            'message' => $isOutOfBounds
                ? 'Check out berhasil dicatat dan sedang menunggu persetujuan atasan karena di luar geofence.'
                : 'Check out berhasil',
            'attendance' => new AttendanceResource($attendance),
        ]);
    }
}
