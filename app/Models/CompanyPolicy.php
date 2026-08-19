<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'late_tolerance_minutes',
        'require_photo',
        'require_gps',
        'require_face_recognition',
        'face_match_threshold',
        'face_fail_action',
        'geofence_latitude',
        'geofence_longitude',
        'geofence_radius_meters',
        'annual_leave_quota',
        'default_approval_stages',
        'work_start_time',
        'work_end_time',
    ];

    protected function casts(): array
    {
        return [
            'require_photo' => 'boolean',
            'require_gps' => 'boolean',
            'require_face_recognition' => 'boolean',
            'face_match_threshold' => 'integer',
            'geofence_latitude' => 'decimal:7',
            'geofence_longitude' => 'decimal:7',
            'late_tolerance_minutes' => 'integer',
            'geofence_radius_meters' => 'integer',
            'annual_leave_quota' => 'integer',
            'default_approval_stages' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
