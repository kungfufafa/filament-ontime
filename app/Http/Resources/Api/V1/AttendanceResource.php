<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'intern_id' => $this->intern_id,
            'freelancer_id' => $this->freelancer_id,
            'date' => $this->date,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'check_in_lat' => $this->check_in_lat,
            'check_in_lng' => $this->check_in_lng,
            'check_out_lat' => $this->check_out_lat,
            'check_out_lng' => $this->check_out_lng,
            'check_in_photo' => $this->check_in_photo ? (config('filesystems.default') === 's3' ? Storage::disk(config('filesystems.default'))->temporaryUrl($this->check_in_photo, now()->addMinutes(60)) : Storage::disk(config('filesystems.default'))->url($this->check_in_photo)) : null,
            'check_out_photo' => $this->check_out_photo ? (config('filesystems.default') === 's3' ? Storage::disk(config('filesystems.default'))->temporaryUrl($this->check_out_photo, now()->addMinutes(60)) : Storage::disk(config('filesystems.default'))->url($this->check_out_photo)) : null,
            'status' => $this->status,
            'is_corrected' => (bool) $this->is_corrected,
            'late_minutes' => $this->late_minutes,
        ];
    }
}
