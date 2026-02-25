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
    Schema::create('class_sessions', function (Blueprint $table) {
        $table->id();
        $table->string('session_id')->unique();
        $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
        $table->foreignId('professor_id')->constrained('users')->cascadeOnDelete();
        $table->timestamp('started_at')->nullable();
        $table->timestamp('ended_at')->nullable();
        $table->enum('status', ['active', 'ended'])->default('active');
        $table->integer('present_count')->default(0);
        $table->integer('absent_count')->default(0);
        $table->integer('late_count')->default(0);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('class_sessions');
}
};
