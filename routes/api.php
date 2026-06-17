<?php

use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\SensorReadingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('sensors')->group(function () {
    Route::get('/', [SensorReadingController::class, 'index']);
    Route::get('/latest', [SensorReadingController::class, 'latest']);
    Route::post('/', [SensorReadingController::class, 'store']);
});

Route::prefix('classifications')->group(function () {
    Route::get('/', [ClassificationController::class, 'index']);
    Route::get('/latest', [ClassificationController::class, 'latest']);
    Route::post('/', [ClassificationController::class, 'store']);
});
