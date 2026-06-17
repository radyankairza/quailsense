<?php

namespace Database\Seeders;

use App\Models\Classification;
use App\Models\SensorReading;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClassificationSeeder extends Seeder
{
    public function run(): void
    {
        Classification::query()->truncate();

        $start = Carbon::now()->subHours(12);
        $rows  = [];

        for ($i = 0; $i < 144; $i++) {
            $ts   = $start->copy()->addMinutes($i * 5);
            $hour = $ts->hour;

            // Aktivitas lebih tinggi di siang hari (jam 10-15)
            $activityChance = ($hour >= 10 && $hour <= 15) ? 0.55 : 0.20;
            $isActive       = (mt_rand(0, 100) / 100) < $activityChance;

            $rows[] = [
                'result'     => $isActive ? 'increased_activity' : 'normal',
                'confidence' => round(mt_rand(75, 98) / 100, 2),
                'device_id'  => 'RPI-001',
                'image_path' => null,
                'created_at' => $ts,
                'updated_at' => $ts,
            ];
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            Classification::query()->insert($chunk);
        }
    }
}
