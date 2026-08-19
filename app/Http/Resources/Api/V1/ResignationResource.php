<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResignationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->employee ?? $this->intern ?? $this->freelancer;

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'intern_id' => $this->intern_id ?? null,
            'freelancer_id' => $this->freelancer_id ?? null,
            'employee_name' => $profile?->full_name ?? $this->employee?->full_name,
            'resignation_date' => $this->resignation_date?->format('Y-m-d'),
            'last_working_day' => $this->last_working_day?->format('Y-m-d'),
            'reason' => $this->reason,
            'handover_notes' => $this->handover_notes,
            'status' => $this->status,
            'current_step' => $this->current_step,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'employee' => $this->employee ? $this->formatWorkerProfile($this->employee) : null,
            'intern' => $this->intern ? $this->formatWorkerProfile($this->intern) : null,
            'freelancer' => $this->freelancer ? $this->formatWorkerProfile($this->freelancer) : null,
            'user' => $profile ? [
                'name' => $profile->full_name ?? 'Pengguna',
                'department' => $profile->division?->name ?? $profile->department ?? 'Staf',
            ] : null,
        ];
    }

    private function formatWorkerProfile($profile): array
    {
        return [
            'id' => $profile->id,
            'full_name' => $profile->full_name ?? 'Pengguna',
            'nip' => $profile->nip ?? $profile->employee_code ?? null,
            'company' => $profile->company ? [
                'id' => $profile->company->id,
                'name' => $profile->company->name,
            ] : null,
            'division' => $profile->division ? [
                'id' => $profile->division->id,
                'name' => $profile->division->name,
            ] : null,
            'master_face_photo' => $profile->master_face_photo ?? null,
        ];
    }
}
