<?php

namespace App\Models;

use App\Traits\HasNormalizedPhone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Intern extends Model
{
    use HasFactory, HasNormalizedPhone;

    protected $fillable = [
        'user_id',
        'company_id',
        'division_id',
        'mentor_id',
        'nis',
        'full_name',
        'institution',
        'email',
        'phone',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'mentor_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
