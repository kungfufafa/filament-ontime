<?php

namespace App\Models;

use App\Traits\HasNormalizedPhone;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasNormalizedPhone, HasRoles, Notifiable;

    public static function findByPhone(string $phone): ?self
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        $variants = array_unique(array_filter([
            $digits,
            str_starts_with($digits, '0') ? '62'.substr($digits, 1) : null,
            str_starts_with($digits, '62') ? '0'.substr($digits, 2) : null,
        ]));

        $user = static::whereIn('phone', $variants)->first();
        if ($user) {
            return $user;
        }

        $employee = Employee::whereIn('phone', $variants)->whereNotNull('user_id')->first();
        if ($employee?->user) {
            return $employee->user;
        }

        $intern = Intern::whereIn('phone', $variants)->whereNotNull('user_id')->first();
        if ($intern?->user) {
            return $intern->user;
        }

        $freelancer = Freelancer::whereIn('phone', $variants)->whereNotNull('user_id')->first();
        if ($freelancer?->user) {
            return $freelancer->user;
        }

        return null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function intern(): HasOne
    {
        return $this->hasOne(Intern::class);
    }

    public function freelancer(): HasOne
    {
        return $this->hasOne(Freelancer::class);
    }

    public function approvers(): HasMany
    {
        return $this->hasMany(Approver::class);
    }

    public function canTrackApprovalProgressFor(Employee $employee): bool
    {
        return $this->hasRole('Employee') && $this->employee?->is($employee);
    }
}
