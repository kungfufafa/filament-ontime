<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_id',
        'date',
        'corrected_check_in',
        'corrected_check_out',
        'reason',
        'attachment',
        'status',
        'current_step',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'corrected_check_in' => 'datetime',
            'corrected_check_out' => 'datetime',
            'current_step' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function approvalSteps(): MorphMany
    {
        return $this->morphMany(ApprovalRequestStep::class, 'approvable');
    }

    public function applyCorrection(): void
    {
        $attendance = Attendance::firstOrNew([
            'employee_id' => $this->employee_id,
            'date' => $this->date,
        ]);

        if ($this->corrected_check_in) {
            $attendance->check_in = $this->corrected_check_in;
        }
        if ($this->corrected_check_out) {
            $attendance->check_out = $this->corrected_check_out;
        }

        $attendance->is_corrected = true;

        if ($this->corrected_check_in && ! $attendance->status) {
            $attendance->status = 'on_time';
        }

        $attendance->save();

        $this->update([
            'status' => 'approved',
            'attendance_id' => $attendance->id,
        ]);
    }
}
