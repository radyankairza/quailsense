<?php

namespace Database\Seeders;

use App\Models\SensorReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SensorReadingSeeder extends Seeder
{
    public function run(): void
    {
        SensorReading::query()->truncate();

        $start  = Carbon::now()->subHours(12);
        $temp   = 27.5;   // nilai awal suhu
        $hum    = 62.0;   // nilai awal kelembapan
        $rows   = [];

        for ($i = 0; $i < 144; $i++) {
            // Drift kecil: suhu naik di siang hari, turun di malam
            $hour   = $start->copy()->addMinutes($i * 5)->hour;
            $target = ($hour >= 10 && $hour <= 15) ? 32.0 : 27.5;
            $temp   = round($temp + ($target - $temp) * 0.04 + (mt_rand(-10, 10) / 100), 1);
            $temp   = max(24.0, min(36.0, $temp));

            // Kelembapan invers terhadap suhu
            $humTarget = 80 - ($temp - 24) * 0.9;
            $hum = round($hum + ($humTarget - $hum) * 0.05 + (mt_rand(-8, 8) / 100), 1);
            $hum = max(40.0, min(85.0, $hum));

            $ts = $start->copy()->addMinutes($i * 5);
            $rows[] = [
                'temperature' => $temp,
                'humidity'    => $hum,
                'device_id'   => 'ESP32-001',
                'created_at'  => $ts,
                'updated_at'  => $ts,
            ];
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            SensorReading::query()->insert($chunk);
        }
    }
}
