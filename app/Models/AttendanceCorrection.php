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
        'intern_id',
        'freelancer_id',
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

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    public function freelancer(): BelongsTo
    {
        return $this->belongsTo(Freelancer::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function getWorkerProfile(): mixed
    {
        return $this->employee ?? $this->intern ?? $this->freelancer;
    }

    public function approvalSteps(): MorphMany
    {
        return $this->morphMany(ApprovalRequestStep::class, 'approvable');
    }

    public function applyCorrection(): void
    {
        $search = ['date' => $this->date];
        if ($this->employee_id) {
            $search['employee_id'] = $this->employee_id;
        } elseif ($this->intern_id) {
            $search['intern_id'] = $this->intern_id;
        } elseif ($this->freelancer_id) {
            $search['freelancer_id'] = $this->freelancer_id;
        }

        $attendance = Attendance::firstOrNew($search);

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
