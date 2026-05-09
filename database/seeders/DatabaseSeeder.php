<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\YearLevel;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Room;
use App\Models\User;
use App\Models\ClassSession;
use App\Models\AttendanceRecord;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        // 1. Admin & Professor
        User::create(['name' => 'Admin', 'email' => 'admin@classguard.edu', 'password' => Hash::make('admin123'), 'role' => 'admin', 'status' => 'active']);
        $professor = User::create(['name' => 'Prof. Santos', 'email' => 'prof@classguard.edu', 'password' => Hash::make('password'), 'role' => 'professor', 'status' => 'active']);

        // 2. Rooms
        $roomIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $roomIds[] = Room::create(['name' => "Room 10{$i}"] )->id;
        }

        // 3. Years, Sections, Subjects, Students
        $years = [
            ['name' => '1st Year', 'code' => '1Y', 'subjects' => ['Programming 1', 'Math 101', 'Physics 101', 'English 1', 'History 1', 'PE 1']],
            ['name' => '2nd Year', 'code' => '2Y', 'subjects' => ['Data Structures', 'Algorithms', 'Database Management', 'Web Development']],
            ['name' => '3rd Year', 'code' => '3Y', 'subjects' => ['Software Engineering', 'Operating Systems', 'Networking']],
            ['name' => '4th Year', 'code' => '4Y', 'subjects' => ['Capstone Project 1', 'Capstone Project 2']],
        ];

        $studentIdCounter = 1;
        $sectionsData = [];
        $usersBatch = [];

        foreach ($years as $yData) {
            $yearLevel = YearLevel::create(['name' => $yData['name'], 'code' => $yData['code']]);
            $yearSections = [];

            for ($s = 1; $s <= 4; $s++) {
                $section = Section::create(['year_level_id' => $yearLevel->id, 'name' => "{$yearLevel->code}-" . ['A', 'B', 'C', 'D'][$s - 1]]);
                $yearSections[] = $section;
                
                for ($st = 1; $st <= 20; $st++) {
                    $idNum = sprintf("2024-%05d", $studentIdCounter++);
                    $usersBatch[] = [
                        'name' => "Student {$idNum}",
                        'email' => "student{$idNum}@classguard.edu",
                        'password' => Hash::make('password'),
                        'role' => 'student',
                        'student_id_number' => $idNum,
                        'year_level_id' => $yearLevel->id,
                        'section_id' => $section->id,
                        'rfid_uid' => substr(md5($idNum), 0, 10),
                        'status' => 'active',
                        'created_at' => now(), 'updated_at' => now(),
                    ];
                }
            }
            $sectionsData[] = ['year_level' => $yearLevel, 'sections' => $yearSections, 'subjects' => $yData['subjects']];
        }

        foreach (array_chunk($usersBatch, 500) as $chunk) User::insert($chunk);
        $allStudentsBySection = User::where('role', 'student')->get()->groupBy('section_id');

        // 4. Subjects & Schedules
        $schedulesToCreate = [];
        foreach ($sectionsData as $yData) {
            foreach ($yData['subjects'] as $subName) {
                $subject = Subject::create([
                    'name' => $subName, 
                    'code' => strtoupper(substr(str_replace(' ', '', $subName), 0, 5)) . '101', 
                    'year_level_id' => $yData['year_level']->id
                ]);
                foreach ($yData['sections'] as $section) {
                    $schedulesToCreate[] = [
                        'subject_id' => $subject->id, 
                        'section_id' => $section->id, 
                        'professor_id' => $professor->id, 
                        'room_id' => $roomIds[array_rand($roomIds)],
                        'schedule_days' => json_encode(['Mon', 'Wed']), 
                        'schedule_start_time' => '08:00', 
                        'schedule_end_time' => '10:00',
                        'late_threshold_minutes' => 15, 
                        'allow_guests' => false, 
                        'class_code' => Str::upper(Str::random(6)),
                        'created_at' => now(), 
                        'updated_at' => now(),
                    ];
                }
            }
        }
        Schedule::insert($schedulesToCreate);
        $schedules = Schedule::all();

        // 5. Sessions & Attendance
        $attendanceBatch = [];
        $now = now();
        $startDate = $now->copy()->subWeeks(12)->startOfWeek();

        foreach ($schedules as $sched) {
            $sectionStudents = $allStudentsBySection[$sched->section_id]->shuffle();
            $inactives = $sectionStudents->splice(0, 2);
            $perfects = $sectionStudents->splice(0, 3);
            $atRisk = $sectionStudents->splice(0, 5);
            $averages = $sectionStudents;

            for ($week = 0; $week < 12; $week++) {
                $weekStart = $startDate->copy()->addWeeks($week);
                $days = [$weekStart->copy(), $weekStart->copy()->addDays(2)]; // Mon, Wed

                foreach ($days as $date) {
                    if ($date->isAfter($now)) continue;

                    $startedAt = $date->copy()->setTime(8, 0);
                    $endedAt = $date->copy()->setTime(10, 0);
                    $present = 0; $late = 0; $absent = 0;
                    $tempAttendance = [];

                    foreach ([$inactives, $perfects, $atRisk, $averages] as $gIdx => $group) {
                        foreach ($group as $stu) {
                            $status = 'absent';
                            if ($gIdx === 0) $status = 'absent';
                            elseif ($gIdx === 1) $status = 'present';
                            elseif ($gIdx === 2) { $r = rand(1, 10); $status = $r <= 4 ? 'present' : ($r <= 6 ? 'late' : 'absent'); }
                            elseif ($gIdx === 3) { $r = rand(1, 100); $status = $r <= 80 ? 'present' : ($r <= 95 ? 'late' : 'absent'); }

                            $time = null;
                            if ($status === 'present') { $present++; $time = $startedAt->copy()->subMinutes(rand(1, 15)); }
                            elseif ($status === 'late') { $late++; $time = $startedAt->copy()->addMinutes(rand(16, 45)); }
                            else { $absent++; }

                            if ($status !== 'absent') {
                                $tempAttendance[] = [
                                    'student_id' => $stu->id, 'status' => $status,
                                    'rfid_uid' => $stu->rfid_uid, 'rfid_scanned_at' => $time,
                                    'created_at' => now(), 'updated_at' => now(),
                                ];
                            }
                        }
                    }

                    $session = ClassSession::create([
                        'schedule_id' => $sched->id, 'professor_id' => $professor->id, 'room_id' => $sched->room_id,
                        'session_id' => Str::random(10), 'started_at' => $startedAt, 'ended_at' => $endedAt, 'status' => 'ended',
                        'present_count' => $present, 'late_count' => $late, 'absent_count' => $absent,
                    ]);

                    foreach ($tempAttendance as $att) {
                        $att['session_id'] = $session->id;
                        $attendanceBatch[] = $att;
                    }

                    if (count($attendanceBatch) >= 1000) {
                        AttendanceRecord::insert($attendanceBatch);
                        $attendanceBatch = [];
                    }
                }
            }
        }
        if (count($attendanceBatch) > 0) AttendanceRecord::insert($attendanceBatch);
    }
}
