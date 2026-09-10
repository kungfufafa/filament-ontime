<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Division $division) {
            if ($division->exists && $division->parent_id) {
                if ((int) $division->parent_id === (int) $division->id) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'Divisi tidak boleh menjadi parent bagi dirinya sendiri.',
                    ]);
                }

                if ($division->isDescendantOf($division->parent_id)) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'Terjadi circular reference! Parent divisi tidak boleh diambil dari sub-divisinya sendiri.',
                    ]);
                }
            }
        });
    }

    public function getDepth(): int
    {
        $depth = 0;
        $current = $this->parent;

        while ($current) {
            $depth++;
            $current = $current->parent;
        }

        return $depth;
    }

    public function isDescendantOf(int $targetId): bool
    {
        $childrenIds = $this->getAllChildrenIds();

        return in_array($targetId, $childrenIds, true);
    }

    public function getAllChildrenIds(): array
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }

        return $ids;
    }

    public function descendantsAndSelf(): Collection
    {
        $ids = array_merge([$this->id], $this->getAllChildrenIds());

        return static::whereIn('id', $ids)->get();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Division::class, 'parent_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function jobTitles(): HasMany
    {
        return $this->hasMany(JobTitle::class);
    }
}
