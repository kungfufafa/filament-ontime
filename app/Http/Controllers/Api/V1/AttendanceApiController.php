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

        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        if ($todayAttendance && $todayAttendance->check_in) {
            return response()->json(['message' => 'Sudah melakukan check in hari ini.'], 422);
        }

        $company = $employee->company;
        $policy = $company?->policy;

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ($policy?->require_gps) {
            if (! $lat || ! $lng) {
                return response()->json(['message' => 'Lokasi GPS wajib diisi.'], 422);
            }

            if ($company->latitude && $company->longitude) {
                $service = new GeofenceService;
                $isWithinRadius = $service->isWithinRadius(
                    (float) $lat,
                    (float) $lng,
                    (float) $company->latitude,
                    (float) $company->longitude,
                    (int) ($policy->geofence_radius_meters ?? 100)
                );

                if (! $isWithinRadius) {
                    return response()->json(['message' => 'Lokasi Anda berada di luar radius lokasi kantor.'], 422);
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

        $status = 'present';
        $lateMinutes = 0;

        if ($now->greaterThan($lateThreshold)) {
            $status = 'late';
            $lateMinutes = (int) $workStart->diffInMinutes($now);
        }

        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => today()->toDateString(),
            ],
            [
                'check_in' => $now->toTimeString(),
                'check_in_latitude' => $lat,
                'check_in_longitude' => $lng,
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

        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        if (! $attendance || ! $attendance->check_in) {
            return response()->json(['message' => 'Anda belum melakukan check in hari ini.'], 422);
        }

        if ($attendance->check_out) {
            return response()->json(['message' => 'Sudah melakukan check out hari ini.'], 422);
        }

        $company = $employee->company;
        $policy = $company?->policy;

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ($policy?->require_gps) {
            if (! $lat || ! $lng) {
                return response()->json(['message' => 'Lokasi GPS wajib diisi.'], 422);
            }

            if ($company->latitude && $company->longitude) {
                $service = new GeofenceService;
                $isWithinRadius = $service->isWithinRadius(
                    (float) $lat,
                    (float) $lng,
                    (float) $company->latitude,
                    (float) $company->longitude,
                    (int) ($policy->geofence_radius_meters ?? 100)
                );

                if (! $isWithinRadius) {
                    return response()->json(['message' => 'Lokasi Anda berada di luar radius lokasi kantor.'], 422);
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
            'check_out_latitude' => $lat,
            'check_out_longitude' => $lng,
            'check_out_photo' => $photoPath,
        ]);

        return response()->json([
            'message' => 'Check out berhasil',
            'attendance' => new AttendanceResource($attendance),
        ]);
    }
}
