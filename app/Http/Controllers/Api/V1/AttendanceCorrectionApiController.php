<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAttendanceCorrectionRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceCorrectionRequest;
use App\Http\Resources\Api\V1\AttendanceCorrectionResource;
use App\Models\AttendanceCorrection;
use App\Services\ApprovalFlowService;
use App\Services\FileNamingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceCorrectionApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $requests = AttendanceCorrection::where('employee_id', $employee->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => AttendanceCorrectionResource::collection($requests),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function store(StoreAttendanceCorrectionRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $identifier = $employee->employee_code ?? (string) $employee->id;
            $attachmentPath = FileNamingService::storeUploadedFile(
                $request->file('attachment'),
                'attendance-corrections/attachments',
                'CORR',
                $identifier,
                'public'
            );
        }

        $correction = AttendanceCorrection::create([
            'employee_id' => $employee->id,
            'date' => $request->input('date'),
            'corrected_check_in' => $request->input('corrected_check_in'),
            'corrected_check_out' => $request->input('corrected_check_out'),
            'reason' => $request->input('reason'),
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        $service = new ApprovalFlowService;
        $service->generateSteps($correction, 'correction');

        return response()->json([
            'message' => 'Pengajuan koreksi absensi berhasil dibuat',
            'data' => new AttendanceCorrectionResource($correction),
        ], 201);
    }

    public function update(UpdateAttendanceCorrectionRequest $request, int $id): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Employee profile not found.'], 422);
        }

        $correction = AttendanceCorrection::where('employee_id', $employee->id)->find($id);

        if (! $correction) {
            return response()->json(['message' => 'Pengajuan koreksi absensi tidak ditemukan.'], 404);
        }

        if ($correction->status !== 'pending') {
            return response()->json(['message' => 'Pengajuan yang sudah diproses tidak dapat diubah.'], 422);
        }

        $data = $request->only(['date', 'corrected_check_in', 'corrected_check_out', 'reason']);

        if ($request->hasFile('attachment')) {
            $identifier = $employee->employee_code ?? (string) $employee->id;
            $data['attachment'] = FileNamingService::storeUploadedFile(
                $request->file('attachment'),
                'attendance-corrections/attachments',
                'CORR',
                $identifier,
                'public'
            );
        }

        $correction->update($data);

        return response()->json([
            'message' => 'Pengajuan koreksi absensi berhasil diperbarui',
            'data' => new AttendanceCorrectionResource($correction),
        ]);
    }
}
