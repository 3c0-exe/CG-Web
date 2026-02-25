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
    Schema::create('subjects', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('code');
        $table->foreignId('year_level_id')->constrained()->cascadeOnDelete();
        $table->foreignId('section_id')->constrained()->cascadeOnDelete();
        $table->foreignId('professor_id')->constrained('users')->cascadeOnDelete();
        $table->string('class_code', 6)->unique();
        $table->boolean('allow_guests')->default(false);
        $table->integer('late_threshold_minutes')->default(15);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
