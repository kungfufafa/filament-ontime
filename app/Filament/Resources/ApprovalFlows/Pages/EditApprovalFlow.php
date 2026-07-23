<?php

namespace App\Filament\Resources\ApprovalFlows\Pages;

use App\Filament\Resources\ApprovalFlows\ApprovalFlowResource;
use App\Models\ApprovalFlow;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditApprovalFlow extends EditRecord
{
    protected static string $resource = ApprovalFlowResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $companyId = $this->record->id;

        $data['company_id'] = $companyId;

        $data['leave_steps'] = ApprovalFlow::where('company_id', $companyId)
            ->where('request_type', 'leave')
            ->orderBy('step_order')
            ->get()
            ->toArray();

        $data['overtime_steps'] = ApprovalFlow::where('company_id', $companyId)
            ->where('request_type', 'overtime')
            ->orderBy('step_order')
            ->get()
            ->toArray();

        $data['correction_steps'] = ApprovalFlow::where('company_id', $companyId)
            ->where('request_type', 'correction')
            ->orderBy('step_order')
            ->get()
            ->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $companyId = $record->id;

        $leaveSteps = $data['leave_steps'] ?? [];
        $overtimeSteps = $data['overtime_steps'] ?? [];
        $correctionSteps = $data['correction_steps'] ?? [];

        $this->validateSteps($leaveSteps, 'Cuti / Izin');
        $this->validateSteps($overtimeSteps, 'Lembur');
        $this->validateSteps($correctionSteps, 'Koreksi Absensi');

        DB::transaction(function () use ($companyId, $leaveSteps, $overtimeSteps, $correctionSteps) {
            ApprovalFlow::where('company_id', $companyId)->delete();

            $this->insertSteps($companyId, 'leave', $leaveSteps);
            $this->insertSteps($companyId, 'overtime', $overtimeSteps);
            $this->insertSteps($companyId, 'correction', $correctionSteps);
        });

        return $record;
    }

    private function validateSteps(array $steps, string $label): void
    {
        if (count($steps) !== 3) {
            throw ValidationException::withMessages([
                'steps' => "Alur {$label} wajib memiliki tiga tahap: Approver, BOD, lalu Superadmin.",
            ]);
        }

        $orders = array_map(fn ($step) => (int) $step['step_order'], $steps);
        sort($orders, SORT_NUMERIC);

        if (count($orders) !== count(array_unique($orders))) {
            throw ValidationException::withMessages([
                'steps' => "Urutan tahap (step_order) pada {$label} tidak boleh ada yang duplikat.",
            ]);
        }

        $expected = [1, 2, 3];
        if ($orders !== $expected) {
            throw ValidationException::withMessages([
                'steps' => "Urutan tahap pada {$label} harus 1 (Approver), 2 (BOD), lalu 3 (Superadmin).",
            ]);
        }

        usort($steps, fn (array $first, array $second): int => $first['step_order'] <=> $second['step_order']);
        $expectedRoles = ['Approver', 'BOD', 'Superadmin'];

        foreach ($steps as $index => $step) {
            if (($step['approver_type'] ?? null) !== 'role' || ($step['approver_role'] ?? null) !== $expectedRoles[$index]) {
                throw ValidationException::withMessages([
                    'steps' => 'Tahap '.($index + 1)." pada {$label} wajib ditangani oleh {$expectedRoles[$index]}.",
                ]);
            }
        }
    }

    private function insertSteps(int $companyId, string $requestType, array $steps): void
    {
        foreach ($steps as $index => $step) {
            $stepOrder = (int) ($step['step_order'] ?? ($index + 1));

            ApprovalFlow::create([
                'company_id' => $companyId,
                'request_type' => $requestType,
                'step_number' => $stepOrder,
                'step_order' => $stepOrder,
                'name' => $step['name'] ?? "Tahap {$stepOrder}",
                'approver_type' => $step['approver_type'] ?? 'role',
                'approver_role' => ($step['approver_type'] ?? 'role') === 'role' ? ($step['approver_role'] ?? 'Approver') : null,
                'user_id' => ($step['approver_type'] ?? 'role') === 'user' ? ($step['user_id'] ?? null) : null,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
