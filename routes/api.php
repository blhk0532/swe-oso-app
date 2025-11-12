<?php

use App\Http\Controllers\Api\DataPrivateController;
use App\Http\Controllers\Api\EniroDataController;
use App\Http\Controllers\Api\HittaDataController;
use App\Http\Controllers\Api\HittaSeController;
use App\Http\Controllers\Api\MerinfoDataController;
use App\Http\Controllers\Api\PostNummerApiController;
use App\Http\Controllers\Api\PostNummerQueController;
use App\Http\Controllers\Api\RatsitDataController;
use App\Http\Controllers\Api\UpplysningDataController;
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

// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type, Authorization');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('data-private', DataPrivateController::class);
    Route::post('/data-private/bulk', [DataPrivateController::class, 'bulkStore']);
});

// Public API routes (no authentication required)
Route::apiResource('hitta-data', HittaDataController::class);
Route::post('/hitta-data/bulk', [HittaDataController::class, 'bulkStore']);

Route::apiResource('eniro-data', EniroDataController::class);
Route::post('/eniro-data/bulk', [EniroDataController::class, 'bulkStore']);

Route::apiResource('upplysning-data', UpplysningDataController::class);
Route::post('/upplysning-data/bulk', [UpplysningDataController::class, 'bulkStore']);

Route::apiResource('ratsit-data', RatsitDataController::class);
Route::post('/ratsit-data/bulk', [RatsitDataController::class, 'bulkStore']);

Route::apiResource('merinfo-data', MerinfoDataController::class);
Route::post('/merinfo-data/bulk', [MerinfoDataController::class, 'bulkStore']);
Route::post('/merinfo-data/bulk-update-totals', [MerinfoDataController::class, 'bulkUpdateTotals']);
// Alias route for legacy client scripts expecting /merinfo/import
Route::post('/merinfo/import', [MerinfoDataController::class, 'bulkStore']);

Route::apiResource('post-nummer', PostNummerApiController::class);
Route::put('/post-nummer/by-code/{postnummer}', [PostNummerApiController::class, 'updateByPostnummer']);
Route::post('/post-nummer/bulk-update', [PostNummerApiController::class, 'bulkUpdateByPostnummer']);
Route::post('/post-nummer/bulk-update-totals', [PostNummerApiController::class, 'bulkUpdateTotals']);

Route::get('/postnummer-que/first', [PostNummerQueController::class, 'getFirstPostNummer']);
Route::post('/postnummer-que/first-next', [PostNummerQueController::class, 'firstNext']);
Route::post('/postnummer-que/process-next', [PostNummerQueController::class, 'processNext']);
Route::apiResource('postnummer-que', PostNummerQueController::class)->only(['update']);
Route::put('/postnummer-que/by-code/{postNummer}', [PostNummerQueController::class, 'updateByPostNummer']);
Route::post('/postnummer-que/bulk-update', [PostNummerQueController::class, 'bulkUpdate']);

Route::post('/hitta-se', [HittaSeController::class, 'store']);
Route::post('/hitta-se/batch', [HittaSeController::class, 'batchStore']);
