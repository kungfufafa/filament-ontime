<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceCorrectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'corrected_check_in' => $this->corrected_check_in,
            'corrected_check_out' => $this->corrected_check_out,
            'reason' => $this->reason,
            'status' => $this->status,
            'current_step' => $this->current_step,
            'total_steps' => $this->total_steps,
            'rejection_note' => $this->rejection_note,
            'attachment' => $this->attachment ? url('storage/'.$this->attachment) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
