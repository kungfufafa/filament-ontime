<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['users', 'employees', 'interns', 'freelancers', 'companies'];

        foreach ($tables as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table) || ! DB::getSchemaBuilder()->hasColumn($table, 'phone')) {
                continue;
            }

            DB::table($table)->whereNotNull('phone')->where('phone', '!=', '')->get()->each(function ($row) use ($table) {
                $raw = $row->phone;
                $digits = preg_replace('/\D/', '', $raw);

                if (empty($digits)) {
                    return;
                }

                if (str_starts_with($digits, '0')) {
                    $normalized = '62'.substr($digits, 1);
                } elseif (str_starts_with($digits, '8')) {
                    $normalized = '62'.$digits;
                } else {
                    $normalized = $digits;
                }

                if ($normalized !== $raw) {
                    DB::table($table)->where('id', $row->id)->update(['phone' => $normalized]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed as standardization is non-destructive
    }
};
