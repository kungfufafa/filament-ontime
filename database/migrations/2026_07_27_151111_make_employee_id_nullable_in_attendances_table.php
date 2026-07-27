<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite tidak mendukung ALTER COLUMN langsung.
        // Gunakan raw SQL untuk menonaktifkan FK constraint sementara lalu rebuild tabel.
        DB::statement('PRAGMA foreign_keys=OFF');

        DB::statement('
            CREATE TABLE "attendances_new" (
                "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
                "employee_id" integer NULL REFERENCES "employees"("id") ON DELETE CASCADE,
                "intern_id" integer NULL REFERENCES "interns"("id") ON DELETE CASCADE,
                "freelancer_id" integer NULL REFERENCES "freelancers"("id") ON DELETE CASCADE,
                "date" date NOT NULL,
                "check_in" datetime NULL,
                "check_out" datetime NULL,
                "check_in_photo" varchar NULL,
                "check_out_photo" varchar NULL,
                "check_in_lat" numeric NULL,
                "check_in_lng" numeric NULL,
                "check_out_lat" numeric NULL,
                "check_out_lng" numeric NULL,
                "status" varchar NOT NULL DEFAULT \'on_time\',
                "late_minutes" integer NOT NULL DEFAULT 0,
                "notes" text NULL,
                "is_corrected" tinyint(1) NOT NULL DEFAULT 0,
                "created_at" datetime NULL,
                "updated_at" datetime NULL
            )
        ');

        DB::statement('
            INSERT INTO "attendances_new"
                (id, employee_id, intern_id, freelancer_id, date, check_in, check_out,
                 check_in_photo, check_out_photo, check_in_lat, check_in_lng,
                 check_out_lat, check_out_lng, status, late_minutes, notes,
                 is_corrected, created_at, updated_at)
            SELECT
                id, employee_id, intern_id, freelancer_id, date, check_in, check_out,
                check_in_photo, check_out_photo, check_in_lat, check_in_lng,
                check_out_lat, check_out_lng, status, late_minutes, notes,
                is_corrected, created_at, updated_at
            FROM "attendances"
        ');

        DB::statement('DROP TABLE "attendances"');
        DB::statement('ALTER TABLE "attendances_new" RENAME TO "attendances"');

        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void
    {
        // Kembalikan employee_id menjadi NOT NULL (jika rollback diperlukan)
        DB::statement('PRAGMA foreign_keys=OFF');

        DB::statement('
            CREATE TABLE "attendances_old" (
                "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
                "employee_id" integer NOT NULL REFERENCES "employees"("id") ON DELETE CASCADE,
                "intern_id" integer NULL REFERENCES "interns"("id") ON DELETE CASCADE,
                "freelancer_id" integer NULL REFERENCES "freelancers"("id") ON DELETE CASCADE,
                "date" date NOT NULL,
                "check_in" datetime NULL,
                "check_out" datetime NULL,
                "check_in_photo" varchar NULL,
                "check_out_photo" varchar NULL,
                "check_in_lat" numeric NULL,
                "check_in_lng" numeric NULL,
                "check_out_lat" numeric NULL,
                "check_out_lng" numeric NULL,
                "status" varchar NOT NULL DEFAULT \'on_time\',
                "late_minutes" integer NOT NULL DEFAULT 0,
                "notes" text NULL,
                "is_corrected" tinyint(1) NOT NULL DEFAULT 0,
                "created_at" datetime NULL,
                "updated_at" datetime NULL
            )
        ');

        DB::statement('INSERT INTO "attendances_old" SELECT * FROM "attendances" WHERE employee_id IS NOT NULL');
        DB::statement('DROP TABLE "attendances"');
        DB::statement('ALTER TABLE "attendances_old" RENAME TO "attendances"');

        DB::statement('PRAGMA foreign_keys=ON');
    }
};
