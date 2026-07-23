<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLeaveRequest;
use App\Http\Requests\Api\V1\UpdateLeaveRequest;
use App\Http\Resources\Api\V1\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\ApprovalFlowService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $requests = LeaveRequest::where('employee_id', $employee->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => LeaveRequestResource::collection($requests),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $daysCount = $startDate->diffInDays($endDate) + 1;

        if ($request->input('leave_type') === 'annual') {
            $policy = $employee->company?->policy;
            $maxQuota = $policy?->annual_leave_quota ?? 12;

            $usedQuota = (int) LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type', 'annual')
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->sum('days_count');

            $remaining = $maxQuota - $usedQuota;

            if ($daysCount > $remaining) {
                return response()->json([
                    'message' => "Kuota cuti tahunan Anda tidak mencukupi (Sisa: {$remaining} hari, Pengajuan: {$daysCount} hari).",
                ], 422);
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-requests/attachments', 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $request->input('leave_type'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'days_count' => $daysCount,
            'reason' => $request->input('reason'),
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        $service = new ApprovalFlowService;
        $service->generateSteps($leaveRequest, 'leave');

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dibuat',
            'data' => new LeaveRequestResource($leaveRequest),
        ], 201);
    }

    public function update(UpdateLeaveRequest $request, int $id): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $leaveRequest = LeaveRequest::where('employee_id', $employee->id)->find($id);

        if (! $leaveRequest) {
            return response()->json(['message' => 'Pengajuan cuti tidak ditemukan.'], 404);
        }

        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Pengajuan yang sudah diproses tidak dapat diubah.'], 422);
        }

        $data = $request->only(['leave_type', 'start_date', 'end_date', 'reason']);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('leave-requests/attachments', 'public');
        }

        if (isset($data['start_date']) || isset($data['end_date'])) {
            $start = Carbon::parse($data['start_date'] ?? $leaveRequest->start_date);
            $end = Carbon::parse($data['end_date'] ?? $leaveRequest->end_date);
            $data['days_count'] = $start->diffInDays($end) + 1;
        }

        $leaveRequest->update($data);

        return response()->json([
            'message' => 'Pengajuan cuti berhasil diperbarui',
            'data' => new LeaveRequestResource($leaveRequest),
        ]);
    }
}
