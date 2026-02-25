<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('attendance_records', function (Blueprint $table) {
        $table->id();
        $table->foreignId('session_id')->constrained('class_sessions')->cascadeOnDelete();
        $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
        $table->string('rfid_uid');
        $table->timestamp('rfid_scanned_at')->nullable();
        $table->timestamp('code_confirmed_at')->nullable();
        $table->enum('status', ['pending', 'present', 'late', 'absent'])->default('pending');
        $table->enum('attendance_type', ['regular', 'guest'])->default('regular');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
