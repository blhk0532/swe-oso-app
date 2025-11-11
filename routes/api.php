<?php

use App\Http\Controllers\Api\DataPrivateController;
use App\Http\Controllers\Api\HittaDataController;
use App\Http\Controllers\Api\HittaSeController;
use App\Http\Controllers\Api\PostNummerApiController;
use App\Http\Controllers\Api\RatsitDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('data-private', DataPrivateController::class);
});

// Public API routes (no authentication required)
Route::apiResource('hitta-data', HittaDataController::class);
Route::apiResource('ratsit-data', RatsitDataController::class);
Route::apiResource('post-nummer', PostNummerApiController::class);
Route::post('/hitta-se', [HittaSeController::class, 'store']);
