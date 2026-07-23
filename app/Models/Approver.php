<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Approver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'division_id',
        'level',
    ];

    protected static function booted(): void
    {
        static::saving(function (Approver $approver) {
            // 1. Enforce Company Requirement
            if (! $approver->company_id) {
                throw ValidationException::withMessages([
                    'company_id' => 'Badan Usaha (Company) wajib diisi.',
                ]);
            }

            // 2. Enforce Division-Company Alignment
            if ($approver->division_id) {
                $division = Division::find($approver->division_id);
                if ($division && (int) $division->company_id !== (int) $approver->company_id) {
                    throw ValidationException::withMessages([
                        'division_id' => 'Divisi yang dipilih tidak terdaftar pada Badan Usaha yang dipilih.',
                    ]);
                }
            }

            // 3. Enforce approval role requirement on User
            $user = User::find($approver->user_id);
            if ($user && ! $user->hasAnyRole(['Approver', 'BOD'])) {
                throw ValidationException::withMessages([
                    'user_id' => 'User yang dipilih harus memiliki role sistem "Approver" atau "BOD".',
                ]);
            }

            // 4. Enforce Uniqueness Constraint: (user_id, company_id, division_id, level)
            $query = static::query()
                ->where('user_id', $approver->user_id)
                ->where('company_id', $approver->company_id)
                ->where('level', $approver->level);

            if ($approver->division_id) {
                $query->where('division_id', $approver->division_id);
            } else {
                $query->whereNull('division_id');
            }

            if ($approver->exists) {
                $query->where('id', '!=', $approver->id);
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => 'Mapping untuk User, Scope (Company/Divisi), dan Level yang sama sudah pernah didaftarkan.',
                ]);
            }
        });
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
}
