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
    Schema::table('users', function (Blueprint $table) {
        $table->enum('role', ['admin', 'professor', 'student'])->default('student')->after('email');
        $table->string('student_id_number')->nullable()->unique()->after('role');
        $table->foreignId('year_level_id')->nullable()->constrained()->nullOnDelete()->after('student_id_number');
        $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete()->after('year_level_id');
        $table->string('rfid_uid')->nullable()->unique()->after('section_id');
        $table->timestamp('rfid_linked_at')->nullable()->after('rfid_uid');
        $table->string('phone')->nullable()->after('rfid_linked_at');
        $table->enum('status', ['pending', 'active', 'inactive'])->default('pending')->after('phone');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'role', 'student_id_number', 'year_level_id',
            'section_id', 'rfid_uid', 'rfid_linked_at',
            'phone', 'status'
        ]);
    });
}
};
