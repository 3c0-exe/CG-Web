<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\YearLevel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Year Levels
        $y1 = YearLevel::create(['name' => '1st Year', 'code' => '1Y']);
        $y2 = YearLevel::create(['name' => '2nd Year', 'code' => '2Y']);
        $y3 = YearLevel::create(['name' => '3rd Year', 'code' => '3Y']);
        $y4 = YearLevel::create(['name' => '4th Year', 'code' => '4Y']);

        // Sections
        $s1 = Section::create(['year_level_id' => $y1->id, 'name' => '101A']);
        $s2 = Section::create(['year_level_id' => $y1->id, 'name' => '101B']);
        $s3 = Section::create(['year_level_id' => $y2->id, 'name' => '201A']);

        // Admin
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@classguard.edu',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        // Professor
        $professor = User::create([
            'name'     => 'Prof. Santos',
            'email'    => 'prof@classguard.edu',
            'password' => Hash::make('password'),
            'role'     => 'professor',
            'status'   => 'active',
        ]);

        // Students
        $student1 = User::create([
            'name'              => 'Juan Dela Cruz',
            'email'             => 'juan@classguard.edu',
            'password'          => Hash::make('password'),
            'role'              => 'student',
            'student_id_number' => '2024-00001',
            'year_level_id'     => $y1->id,
            'section_id'        => $s1->id,
            'rfid_uid'          => 'A1:B2:C3:D4',
            'status'            => 'active',
        ]);

        $student2 = User::create([
            'name'              => 'Maria Santos',
            'email'             => 'maria@classguard.edu',
            'password'          => Hash::make('password'),
            'role'              => 'student',
            'student_id_number' => '2024-00002',
            'year_level_id'     => $y1->id,
            'section_id'        => $s1->id,
            'rfid_uid'          => 'E5:F6:G7:H8',
            'status'            => 'active',
        ]);

        // Subjects
        Subject::create([
            'name'                  => 'Introduction to Computing',
            'code'                  => 'ITC101',
            'year_level_id'         => $y1->id,
            'section_id'            => $s1->id,
            'professor_id'          => $professor->id,
            'class_code'            => 'ITC001',
            'allow_guests'          => true,
            'late_threshold_minutes'=> 15,
        ]);

        Subject::create([
            'name'                  => 'Computer Programming 1',
            'code'                  => 'CP101',
            'year_level_id'         => $y1->id,
            'section_id'            => $s1->id,
            'professor_id'          => $professor->id,
            'class_code'            => 'CP0001',
            'allow_guests'          => false,
            'late_threshold_minutes'=> 10,
        ]);
    }
}
