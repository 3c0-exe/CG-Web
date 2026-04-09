<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\ClassSession;
use App\Models\User;
use App\Models\AttendanceRecord;

class MqttListener extends Command
{
    protected $signature   = 'mqtt:listen';
    protected $description = 'Listen for MQTT messages from ESP32 RFID readers';

    public function handle(): void
    {
        $host     = env('MQTT_HOST');
        $port     = (int) env('MQTT_PORT', 8883);
        $username = env('MQTT_USERNAME');
        $password = env('MQTT_PASSWORD');

        $this->info('Connecting to HiveMQ broker...');

        $client = new MqttClient($host, $port, 'laravel-listener');

        $settings = (new ConnectionSettings())
            ->setUsername($username)
            ->setPassword($password)
            ->setUseTls(true)
            ->setTlsSelfSignedAllowed(true)
            ->setKeepAliveInterval(60);

        $client->connect($settings, true);
        $this->info('Connected! Listening for card scans...');

        // Subscribe to raw card scans from ESP32
        $client->subscribe('attendance/scan/raw', function (string $topic, string $message) {
            $data = json_decode($message, true);

            if (!isset($data['session_id'], $data['uid'])) {
                $this->warn('Invalid message received: ' . $message);
                return;
            }

            $sessionId = $data['session_id'];
            $uid       = strtoupper($data['uid']);

            $this->info("Card scanned: {$uid} for session {$sessionId}");

            // Find active session (load subject relationship for late threshold)
            $session = ClassSession::where('session_id', $sessionId)
                ->where('status', 'active')
                ->with('subject')
                ->first();

            if (!$session) {
                $this->warn("No active session found: {$sessionId}");
                return;
            }

            // Find student by RFID UID
            $student = User::where('rfid_uid', $uid)->first();

            if (!$student) {
                $this->warn("No student found with UID: {$uid}");
                return;
            }

            // Prevent duplicate scan
            $existing = AttendanceRecord::where('session_id', $session->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existing) {
                $this->warn("Already scanned: {$student->name}");
                return;
            }

            // ✨ DETERMINE STATUS IMMEDIATELY (just like scanCard does)
            $threshold = $session->subject->late_threshold_minutes ?? 15;
            $minutesLate = now()->diffInMinutes($session->started_at);
            $status = $minutesLate > $threshold ? 'late' : 'present';

            // Create attendance record with calculated status
            AttendanceRecord::create([
                'session_id'        => $session->id,
                'student_id'        => $student->id,
                'rfid_uid'          => $uid,
                'rfid_scanned_at'   => now(),
                'status'            => $status, // ✨ 'present' or 'late', NOT 'pending'
                'attendance_type'   => 'regular',
            ]);

            $this->info("✅ Recorded attendance for: {$student->name} - Status: {$status}");

        }, 0);

        // Keep listening forever
        $client->loop(true);
    }
}