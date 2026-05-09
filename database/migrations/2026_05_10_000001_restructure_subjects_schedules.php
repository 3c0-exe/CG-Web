<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create schedules table
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('professor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->json('schedule_days')->nullable();
            $table->time('schedule_start_time')->nullable();
            $table->time('schedule_end_time')->nullable();
            $table->integer('late_threshold_minutes')->default(15);
            $table->boolean('allow_guests')->default(false);
            $table->string('class_code', 6)->unique()->nullable();
            $table->timestamps();
        });

        // 2. Migrate existing subject data into schedules
        $subjects = DB::table('subjects')->get();
        foreach ($subjects as $subject) {
            DB::table('schedules')->insert([
                'subject_id'            => $subject->id,
                'section_id'            => $subject->section_id,
                'professor_id'          => $subject->professor_id,
                'room_id'               => null,
                'schedule_days'         => $subject->schedule_days,
                'schedule_start_time'   => $subject->schedule_start_time,
                'schedule_end_time'     => $subject->schedule_end_time,
                'late_threshold_minutes'=> $subject->late_threshold_minutes,
                'allow_guests'          => $subject->allow_guests,
                'class_code'            => $subject->class_code,
                'created_at'            => $subject->created_at,
                'updated_at'            => $subject->updated_at,
            ]);
        }

        // 3. Add schedule_id to enrollments & class_sessions
        Schema::table('enrollments', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_id')->nullable()->after('subject_id');
        });
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_id')->nullable()->after('subject_id');
        });

        // 4. Populate schedule_id from old subject_id mapping
        $schedules = DB::table('schedules')->get();
        foreach ($schedules as $schedule) {
            DB::table('enrollments')
                ->where('subject_id', $schedule->subject_id)
                ->update(['schedule_id' => $schedule->id]);

            DB::table('class_sessions')
                ->where('subject_id', $schedule->subject_id)
                ->update(['schedule_id' => $schedule->id]);
        }

        // 5. Drop old subject_id FK and column from enrollments
        Schema::table('enrollments', function (Blueprint $table) {
            // MySQL uses the unique index for the student_id foreign key because student_id is the prefix.
            // We must add a standalone index for student_id before dropping the unique index.
            $table->index('student_id');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropUnique(['student_id', 'subject_id']); // old unique
            $table->dropColumn('subject_id');
            
            $table->foreign('schedule_id')->references('id')->on('schedules')->cascadeOnDelete();
        });

        // Re-add unique on new columns
        Schema::table('enrollments', function (Blueprint $table) {
            $table->unique(['student_id', 'schedule_id']);
        });

        // 6. Drop old subject_id FK and column from class_sessions
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
            $table->foreign('schedule_id')->references('id')->on('schedules')->cascadeOnDelete();
        });

        // 7. Clean up subjects table — remove schedule/section/professor columns
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropForeign(['professor_id']);
            $table->dropColumn([
                'section_id', 'professor_id', 'class_code',
                'allow_guests', 'late_threshold_minutes',
                'schedule_days', 'schedule_start_time', 'schedule_end_time',
            ]);
        });
    }

    public function down(): void
    {
        // Reverse is complex — add back columns to subjects, migrate data back, drop schedules
        // For safety, just drop and rebuild if rolling back
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('professor_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('class_code', 6)->unique()->nullable();
            $table->boolean('allow_guests')->default(false);
            $table->integer('late_threshold_minutes')->default(15);
            $table->json('schedule_days')->nullable();
            $table->time('schedule_start_time')->nullable();
            $table->time('schedule_end_time')->nullable();
        });

        Schema::table('class_sessions', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'schedule_id']);
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
            $table->unique(['student_id', 'subject_id']);
        });

        Schema::dropIfExists('schedules');
    }
};
