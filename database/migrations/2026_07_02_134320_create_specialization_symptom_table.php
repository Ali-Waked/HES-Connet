<?php

declare(strict_types=1);

use App\Models\Specialization;
use App\Models\Staff;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create pivot table
        Schema::create('specialization_symptom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialization_id')->constrained('specializations')->cascadeOnDelete();
            $table->foreignId('symptom_id')->constrained('symptoms')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['specialization_id', 'symptom_id']);
        });

        // 2. Add specialization_id to staff (nullable initially)
        Schema::table('staff', function (Blueprint $table) {
            $table->foreignId('specialization_id')->nullable()->constrained('specializations')->nullOnDelete();
        });

        // 3. Migrate data: extract unique JSON specializations into specializations table
        DB::table('staff')
            ->whereNotNull('specialization')
            ->select('specialization')
            ->distinct()
            ->get()
            ->each(function ($row) {
                $specialization = json_decode($row->specialization, true);

                if (! is_array($specialization)) {
                    return;
                }

                $name = [];

                foreach (['en', 'ar'] as $locale) {
                    if (isset($specialization[$locale]) && is_string($specialization[$locale])) {
                        $name[$locale] = trim($specialization[$locale]);
                    }
                }

                if (empty($name)) {
                    return;
                }

                // Avoid duplicates (same English name)
                $exists = DB::table('specializations')
                    ->where('name->en', $name['en'] ?? null)
                    ->exists();

                if (! $exists) {
                    DB::table('specializations')->insert([
                        'uuid' => (string) Str::uuid(),
                        'name' => json_encode($name, JSON_UNESCAPED_UNICODE),
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        // 4. Assign each staff member to their specialization
        DB::table('staff')
            ->whereNotNull('specialization')
            ->get()
            ->each(function ($staff) {
                $specialization = json_decode($staff->specialization, true);

                if (! is_array($specialization)) {
                    return;
                }

                $nameEn = trim($specialization['en'] ?? '');

                if (empty($nameEn)) {
                    return;
                }

                $specializationRow = DB::table('specializations')
                    ->where('name->en', $nameEn)
                    ->first();

                if ($specializationRow) {
                    DB::table('staff')
                        ->where('id', $staff->id)
                        ->update(['specialization_id' => $specializationRow->id]);
                }
            });

        // 5. Migrate facility_staff_symptom data to specialization_symptom
        if (Schema::hasTable('facility_staff_symptom')) {
            $pivotRows = DB::table('facility_staff_symptom')
                ->join('facility_staff', 'facility_staff_symptom.facility_staff_id', '=', 'facility_staff.id')
                ->join('staff', 'facility_staff.staff_id', '=', 'staff.id')
                ->whereNotNull('staff.specialization_id')
                ->select(['staff.specialization_id', 'facility_staff_symptom.symptom_id'])
                ->distinct()
                ->get();

            if ($pivotRows->isNotEmpty()) {
                // Use insertOrIgnore to handle duplicates
                DB::table('specialization_symptom')->insertOrIgnore(
                    $pivotRows->map(fn ($row) => [
                        'specialization_id' => $row->specialization_id,
                        'symptom_id' => $row->symptom_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->toArray()
                );
            }
        }

        // 6. Drop the old JSON specialization column from staff
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('specialization');
        });

        // 7. Drop old pivot tables
        Schema::dropIfExists('facility_staff_symptom');
        Schema::dropIfExists('doctor_symptom');
    }

    public function down(): void
    {
        // Restore doctor_symptom table
        if (! Schema::hasTable('doctor_symptom')) {
            Schema::create('doctor_symptom', function (Blueprint $table) {
                $table->id();
                $table->foreignId('symptom_id')->constrained('symptoms')->cascadeOnDelete();
                $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['symptom_id', 'staff_id']);
            });
        }

        // Restore facility_staff_symptom table
        if (! Schema::hasTable('facility_staff_symptom')) {
            Schema::create('facility_staff_symptom', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facility_staff_id')->constrained('facility_staff')->cascadeOnDelete();
                $table->foreignId('symptom_id')->constrained('symptoms')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['facility_staff_id', 'symptom_id']);
            });
        }

        // Restore specialization JSON column on staff
        Schema::table('staff', function (Blueprint $table) {
            $table->json('specialization')->nullable()->after('user_id');
        });

        // Migrate data back: specialization name back to JSON column
        DB::table('staff')
            ->whereNotNull('specialization_id')
            ->get()
            ->each(function ($staff) {
                $spec = DB::table('specializations')->where('id', $staff->specialization_id)->first();

                if ($spec) {
                    DB::table('staff')
                        ->where('id', $staff->id)
                        ->update(['specialization' => $spec->name]);
                }
            });

        // Migrate specialization_symptom back to facility_staff_symptom
        $specializationSymptoms = DB::table('specialization_symptom')->get();

        foreach ($specializationSymptoms as $row) {
            $staffIds = DB::table('staff')
                ->where('specialization_id', $row->specialization_id)
                ->pluck('id');

            foreach ($staffIds as $staffId) {
                $facilityStaffIds = DB::table('facility_staff')
                    ->where('staff_id', $staffId)
                    ->pluck('id');

                foreach ($facilityStaffIds as $facilityStaffId) {
                    DB::table('facility_staff_symptom')->insertOrIgnore([
                        'facility_staff_id' => $facilityStaffId,
                        'symptom_id' => $row->symptom_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Remove specialization_id from staff
        Schema::table('staff', function (Blueprint $table) {
            $table->dropConstrainedForeignId('specialization_id');
        });

        // Drop the specialization_symptom table
        Schema::dropIfExists('specialization_symptom');
    }
};
