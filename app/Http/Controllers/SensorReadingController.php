<?php

namespace App\Http\Controllers;

use App\Models\SensorReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SensorReadingController extends Controller
{
    public function index(): JsonResponse
    {
        $data = SensorReading::latest()->take(50)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function latest(): JsonResponse
    {
        $data = SensorReading::latest()->first();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'temperature' => ['required', 'numeric'],
            'humidity' => ['required', 'numeric'],
            'device_id' => ['required', 'string'],
        ]);

        $reading = SensorReading::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Sensor data saved',
            'data' => $reading,
        ], 201);
    }
}
