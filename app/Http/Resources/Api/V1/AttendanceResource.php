<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'check_in_latitude' => $this->check_in_latitude,
            'check_in_longitude' => $this->check_in_longitude,
            'check_out_latitude' => $this->check_out_latitude,
            'check_out_longitude' => $this->check_out_longitude,
            'check_in_photo' => $this->check_in_photo ? url('storage/'.$this->check_in_photo) : null,
            'check_out_photo' => $this->check_out_photo ? url('storage/'.$this->check_out_photo) : null,
            'status' => $this->status,
            'is_corrected' => (bool) $this->is_corrected,
            'late_minutes' => $this->late_minutes,
        ];
    }
}
