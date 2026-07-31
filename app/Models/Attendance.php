<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'intern_id',
        'freelancer_id',
        'date',
        'check_in',
        'check_out',
        'check_in_photo',
        'check_out_photo',
        'check_in_lat',
        'check_in_lng',
        'check_out_lat',
        'check_out_lng',
        'status',
        'late_minutes',
        'notes',
        'is_corrected',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'check_in_lat' => 'decimal:7',
            'check_in_lng' => 'decimal:7',
            'check_out_lat' => 'decimal:7',
            'check_out_lng' => 'decimal:7',
            'is_corrected' => 'boolean',
            'status' => AttendanceStatus::class,
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

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

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Scope to filter attendances belonging to the given User's linked worker profile.
     * Handles employee, intern, and freelancer profiles transparently.
     */
    public function scopeByWorker(Builder $query, User $user): Builder
    {
        if ($user->employee_id || $user->employee) {
            $employeeId = $user->employee_id ?? $user->employee?->id;

            return $query->where('employee_id', $employeeId);
        }

        if ($user->intern) {
            return $query->where('intern_id', $user->intern->id);
        }

        if ($user->freelancer) {
            return $query->where('freelancer_id', $user->freelancer->id);
        }

        // Return empty result if no profile linked
        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope to filter attendances for today's date.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('date', today());
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Resolve the worker identifier array for creating/finding an attendance record.
     * Returns exactly one non-null FK mapping.
     *
     * @return array{employee_id?: int, intern_id?: int, freelancer_id?: int}
     */
    public static function workerKeys(User $user): array
    {
        if ($user->employee) {
            return ['employee_id' => $user->employee->id, 'intern_id' => null, 'freelancer_id' => null];
        }

        if ($user->intern) {
            return ['employee_id' => null, 'intern_id' => $user->intern->id, 'freelancer_id' => null];
        }

        if ($user->freelancer) {
            return ['employee_id' => null, 'intern_id' => null, 'freelancer_id' => $user->freelancer->id];
        }

        return [];
    }
}
