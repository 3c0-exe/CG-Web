<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttService
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->host     = env('MQTT_HOST');
        $this->port     = (int) env('MQTT_PORT', 8883);
        $this->username = env('MQTT_USERNAME');
        $this->password = env('MQTT_PASSWORD');
    }

    private function connect(): MqttClient
    {
        $client = new MqttClient($this->host, $this->port, 'laravel-' . uniqid());

        $settings = (new ConnectionSettings())
            ->setUsername($this->username)
            ->setPassword($this->password)
            ->setUseTls(true)
            ->setTlsSelfSignedAllowed(true);

        $client->connect($settings, true);

        return $client;
    }

    public function publish(string $topic, array $message): void
    {
        try {
            $client = $this->connect();
            $client->publish($topic, json_encode($message), 0);
            $client->disconnect();
        } catch (\Exception $e) {
            \Log::error('MQTT publish error: ' . $e->getMessage());
        }
    }

    public function sessionStart($session): void
    {
        $this->publish('attendance/session/start', [
            'session_id' => $session->session_id,
            'subject'    => $session->subject->name,
            'section'    => $session->subject->section->name ?? '',
        ]);
    }

    public function sessionEnd($session): void
    {
        $this->publish('attendance/session/end', [
            'session_id' => $session->session_id,
        ]);
    }

    public function enrollStart(): void
    {
        $this->publish('attendance/enroll/start', [
            'ts' => now()->timestamp,
        ]);
    }

    public function enrollEnd(): void
    {
        $this->publish('attendance/enroll/end', [
            'ts' => now()->timestamp,
        ]);
    }
}