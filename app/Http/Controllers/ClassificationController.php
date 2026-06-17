<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassificationController extends Controller
{
    public function index(): JsonResponse
    {
        $data = Classification::latest()->take(50)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function latest(): JsonResponse
    {
        $data = Classification::latest()->first();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'result' => ['required', 'in:normal,increased_activity'],
            'confidence' => ['required', 'numeric', 'min:0', 'max:1'],
            'device_id' => ['required', 'string'],
            'image_path' => ['nullable', 'string'],
        ]);

        $classification = Classification::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Classification saved',
            'data' => $classification,
        ], 201);
    }
}
