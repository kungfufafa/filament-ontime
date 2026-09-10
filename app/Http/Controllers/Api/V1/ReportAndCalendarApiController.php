<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportAndCalendarApiController extends Controller
{
    public function laporanAbsensi(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $companyId = $request->input('company_id');

        $user = $request->user();
        $query = Attendance::with(['employee.user', 'intern.user', 'freelancer.user', 'employee.company', 'intern.company', 'freelancer.company'])
            ->whereBetween('date', [$startDate, $endDate]);

        if (! $user->can('ViewAny:Attendance')) {
            $query->byWorker($user);
        }

        if ($companyId) {
            $query->where(function ($q) use ($companyId) {
                $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId))
                    ->orWhereHas('intern', fn ($i) => $i->where('company_id', $companyId))
                    ->orWhereHas('freelancer', fn ($f) => $f->where('company_id', $companyId));
            });
        }

        $allRecords = (clone $query)->get();

        $totalPresent = $allRecords->filter(function ($a) {
            $val = $a->status instanceof \BackedEnum ? $a->status->value : (string) $a->status;

            return in_array($val, ['on_time', 'late']) || ! empty($a->check_in);
        })->count();

        // Hitung total frekuensi terlambat
        $totalLateCount = $allRecords->filter(function ($a) {
            $val = $a->status instanceof \BackedEnum ? $a->status->value : (string) $a->status;

            return $val === 'late' || (int) $a->late_minutes > 0;
        })->count();

        // Absen eksplisit dari record database
        $explicitAbsent = $allRecords->filter(function ($a) {
            $val = $a->status instanceof \BackedEnum ? $a->status->value : (string) $a->status;

            return in_array($val, ['absent', 'rejected']);
        })->count();

        $existingDates = $allRecords->pluck('date')->map(function ($d) {
            return Carbon::parse($d)->toDateString();
        })->toArray();

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $effectiveEnd = $end->isFuture() ? today() : $end;
        $unrecordedWeekdayAbsent = 0;

        if ($start->lte($effectiveEnd)) {
            $cursor = clone $start;
            while ($cursor->lte($effectiveEnd)) {
                if ($cursor->isWeekday()) {
                    $dateStr = $cursor->toDateString();
                    if (! in_array($dateStr, $existingDates)) {
                        $unrecordedWeekdayAbsent++;
                    }
                }
                $cursor->addDay();
            }
        }

        $totalAbsent = $explicitAbsent + $unrecordedWeekdayAbsent;

        $attendances = $query->latest('date')->paginate(30);

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'summary' => [
                'total_present' => $totalPresent,
                'total_late' => $totalLateCount,
                'total_absent' => $totalAbsent,
            ],
            'data' => AttendanceResource::collection($attendances->items()),
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
        ]);
    }
}
