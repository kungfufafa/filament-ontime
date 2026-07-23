<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOvertimeRequest;
use App\Http\Requests\Api\V1\UpdateOvertimeRequest;
use App\Http\Resources\Api\V1\OvertimeRequestResource;
use App\Models\OvertimeRequest;
use App\Services\ApprovalFlowService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OvertimeRequestApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $requests = OvertimeRequest::where('employee_id', $employee->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => OvertimeRequestResource::collection($requests),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function store(StoreOvertimeRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $startTime = Carbon::parse($request->input('start_time'));
        $endTime = Carbon::parse($request->input('end_time'));
        $durationMinutes = (int) $startTime->diffInMinutes($endTime);

        $overtimeRequest = OvertimeRequest::create([
            'employee_id' => $employee->id,
            'date' => $request->input('date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'duration_minutes' => $durationMinutes,
            'reason' => $request->input('reason'),
            'status' => 'pending',
        ]);

        $service = new ApprovalFlowService;
        $service->generateSteps($overtimeRequest, 'overtime');

        return response()->json([
            'message' => 'Pengajuan lembur berhasil dibuat',
            'data' => new OvertimeRequestResource($overtimeRequest),
        ], 201);
    }

    public function update(UpdateOvertimeRequest $request, int $id): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $overtimeRequest = OvertimeRequest::where('employee_id', $employee->id)->find($id);

        if (! $overtimeRequest) {
            return response()->json(['message' => 'Pengajuan lembur tidak ditemukan.'], 404);
        }

        if ($overtimeRequest->status !== 'pending') {
            return response()->json(['message' => 'Pengajuan yang sudah diproses tidak dapat diubah.'], 422);
        }

        $data = $request->only(['date', 'start_time', 'end_time', 'reason']);

        if (isset($data['start_time']) || isset($data['end_time'])) {
            $start = Carbon::parse($data['start_time'] ?? $overtimeRequest->start_time);
            $end = Carbon::parse($data['end_time'] ?? $overtimeRequest->end_time);
            $data['duration_minutes'] = (int) $start->diffInMinutes($end);
        }

        $overtimeRequest->update($data);

        return response()->json([
            'message' => 'Pengajuan lembur berhasil diperbarui',
            'data' => new OvertimeRequestResource($overtimeRequest),
        ]);
    }
}
