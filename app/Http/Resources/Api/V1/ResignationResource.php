<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResignationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->full_name,
            'resignation_date' => $this->resignation_date?->format('Y-m-d'),
            'last_working_day' => $this->last_working_day?->format('Y-m-d'),
            'reason' => $this->reason,
            'handover_notes' => $this->handover_notes,
            'status' => $this->status,
            'current_step' => $this->current_step,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
