<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ResignationResource;
use App\Models\Resignation;
use App\Services\ApprovalFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResignationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        $query = Resignation::with('employee');

        if ($request->user()->hasRole('Employee') && $employee) {
            $query->where('employee_id', $employee->id);
        }

        $resignations = $query->latest()->paginate(15);

        return response()->json([
            'data' => ResignationResource::collection($resignations),
            'pagination' => [
                'current_page' => $resignations->currentPage(),
                'last_page' => $resignations->lastPage(),
                'total' => $resignations->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Profil Karyawan tidak ditemukan.'], 422);
        }

        $existing = Resignation::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Anda sudah memiliki pengajuan pengunduran diri yang sedang diproses atau telah disetujui.'], 422);
        }

        $validated = $request->validate([
            'resignation_date' => 'required|date',
            'last_working_day' => 'required|date|after_or_equal:resignation_date',
            'reason' => 'required|string',
            'handover_notes' => 'nullable|string',
        ]);

        $resignation = Resignation::create([
            'employee_id' => $employee->id,
            'resignation_date' => $validated['resignation_date'],
            'last_working_day' => $validated['last_working_day'],
            'reason' => $validated['reason'],
            'handover_notes' => $validated['handover_notes'] ?? null,
            'status' => 'pending',
        ]);

        $service = new ApprovalFlowService;
        $service->generateSteps($resignation, 'resignation');

        return response()->json([
            'message' => 'Pengajuan pengunduran diri berhasil dibuat.',
            'data' => new ResignationResource($resignation),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $resignation = Resignation::with('employee')->find($id);

        if (! $resignation) {
            return response()->json(['message' => 'Pengajuan pengunduran diri tidak ditemukan.'], 404);
        }

        $employee = $request->user()->employee;
        if ($request->user()->hasRole('Employee') && $employee && $resignation->employee_id !== $employee->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke pengajuan ini.'], 403);
        }

        return response()->json([
            'data' => new ResignationResource($resignation),
        ]);
    }
}
