<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportAndCalendarApiController extends Controller
{
    public function kalenderCuti(Request $request): JsonResponse
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $companyId = $request->input('company_id');

        $query = LeaveRequest::with(['employee.company', 'employee.division', 'intern.company', 'intern.division', 'freelancer.company', 'freelancer.division'])
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month);

        if ($companyId) {
            $query->where(function ($q) use ($companyId) {
                $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId))
                    ->orWhereHas('intern', fn ($i) => $i->where('company_id', $companyId))
                    ->orWhereHas('freelancer', fn ($f) => $f->where('company_id', $companyId));
            });
        }

        $leaveRequests = $query->get()->map(function ($leave) {
            $person = $leave->employee ?? $leave->intern ?? $leave->freelancer;

            return [
                'id' => $leave->id,
                'person_name' => $person?->full_name,
                'company_name' => $person?->company?->name,
                'leave_type' => $leave->leave_type,
                'start_date' => $leave->start_date?->format('Y-m-d'),
                'end_date' => $leave->end_date?->format('Y-m-d'),
                'days_count' => $leave->days_count,
                'status' => $leave->status,
                'reason' => $leave->reason,
            ];
        });

        return response()->json([
            'month' => $month,
            'year' => $year,
            'data' => $leaveRequests,
        ]);
    }

    public function laporanAbsensi(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $companyId = $request->input('company_id');

        $query = Attendance::with(['employee.user', 'intern.user', 'freelancer.user', 'employee.company', 'intern.company', 'freelancer.company'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($companyId) {
            $query->where(function ($q) use ($companyId) {
                $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId))
                    ->orWhereHas('intern', fn ($i) => $i->where('company_id', $companyId))
                    ->orWhereHas('freelancer', fn ($f) => $f->where('company_id', $companyId));
            });
        }

        $attendances = $query->latest('date')->paginate(30);

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'data' => $attendances,
        ]);
    }
}
