<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasNormalizedPhone
{
    /**
     * Automatically normalize Indonesian phone numbers to 628... format upon saving.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: function (?string $value): ?string {
                if (empty($value)) {
                    return null;
                }

                $digits = preg_replace('/\D/', '', $value);
                if (empty($digits)) {
                    return null;
                }

                if (str_starts_with($digits, '0')) {
                    return '62'.substr($digits, 1);
                }

                if (str_starts_with($digits, '8')) {
                    return '62'.$digits;
                }

                return $digits;
            }
        );
    }
}
