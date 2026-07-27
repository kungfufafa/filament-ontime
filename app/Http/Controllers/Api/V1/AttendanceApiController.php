<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckInRequest;
use App\Http\Requests\Api\V1\CheckOutRequest;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Models\Attendance;
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

        $todayAttendance = Attendance::query()
            ->where(function ($q) use ($employee, $intern, $freelancer) {
                if ($employee) {
                    $q->where('employee_id', $employee->id);
                } elseif ($intern) {
                    $q->where('intern_id', $intern->id);
                } elseif ($freelancer) {
                    $q->where('freelancer_id', $freelancer->id);
                }
            })
            ->whereDate('date', today())
            ->first();

        if ($todayAttendance && $todayAttendance->check_in) {
            return response()->json(['message' => 'Sudah melakukan check in hari ini.'], 422);
        }

        $company = $profile->company;
        $policy = $company?->policy;

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ($policy?->require_gps) {
            if (! $lat || ! $lng) {
                return response()->json(['message' => 'Lokasi GPS wajib diisi.'], 422);
            }

            if ($company) {
                $geofenceResult = GeofenceService::validateCompanyGeofence($company, (float) $lat, (float) $lng);

                if (! $geofenceResult['is_valid']) {
                    return response()->json(['message' => $geofenceResult['message']], 422);
                }
            }
        }

        if ($policy?->require_photo && ! $request->hasFile('photo')) {
            return response()->json(['message' => 'Foto selfie absensi wajib diunggah.'], 422);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance/photos', 'public');
        }

        $now = now();
        $workStartStr = $policy?->work_start_time ?? '08:00:00';
        $workStart = Carbon::parse($todayAttendance?->date ?? today()->toDateString().' '.$workStartStr);
        $toleranceMinutes = $policy?->late_tolerance_minutes ?? 15;
        $lateThreshold = (clone $workStart)->addMinutes($toleranceMinutes);

        $status = 'on_time';
        $lateMinutes = 0;

        if ($now->greaterThan($lateThreshold)) {
            $status = 'late';
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
            ]
        );

        return response()->json([
            'message' => 'Check in berhasil',
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

        $attendance = Attendance::query()
            ->where(function ($q) use ($employee, $intern, $freelancer) {
                if ($employee) {
                    $q->where('employee_id', $employee->id);
                } elseif ($intern) {
                    $q->where('intern_id', $intern->id);
                } elseif ($freelancer) {
                    $q->where('freelancer_id', $freelancer->id);
                }
            })
            ->whereDate('date', today())
            ->first();

        if (! $attendance || ! $attendance->check_in) {
            return response()->json(['message' => 'Anda belum melakukan check in hari ini.'], 422);
        }

        if ($attendance->check_out) {
            return response()->json(['message' => 'Sudah melakukan check out hari ini.'], 422);
        }

        $company = $profile->company;
        $policy = $company?->policy;

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ($policy?->require_gps) {
            if (! $lat || ! $lng) {
                return response()->json(['message' => 'Lokasi GPS wajib diisi.'], 422);
            }

            if ($company) {
                $geofenceResult = GeofenceService::validateCompanyGeofence($company, (float) $lat, (float) $lng);

                if (! $geofenceResult['is_valid']) {
                    return response()->json(['message' => $geofenceResult['message']], 422);
                }
            }
        }

        if ($policy?->require_photo && ! $request->hasFile('photo')) {
            return response()->json(['message' => 'Foto selfie absensi wajib diunggah.'], 422);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance/photos', 'public');
        }

        $attendance->update([
            'check_out' => now()->toTimeString(),
            'check_out_lat' => $lat,
            'check_out_lng' => $lng,
            'check_out_photo' => $photoPath,
        ]);

        return response()->json([
            'message' => 'Check out berhasil',
            'attendance' => new AttendanceResource($attendance),
        ]);
    }
}
