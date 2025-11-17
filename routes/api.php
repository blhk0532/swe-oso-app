<?php

use App\Http\Controllers\Api\DataPrivateController;
use App\Http\Controllers\Api\EniroDataController;
use App\Http\Controllers\Api\HittaDataController;
use App\Http\Controllers\Api\HittaForetagQueueController;
use App\Http\Controllers\Api\HittaQueueController;
use App\Http\Controllers\Api\HittaSeController;
use App\Http\Controllers\Api\MerinfoForetagQueueController;
use App\Http\Controllers\Api\MerinfoQueueController;
use App\Http\Controllers\Api\PostNummerApiController;
use App\Http\Controllers\Api\PostNummerForetagQueueController;
use App\Http\Controllers\Api\PostNummerQueController;
use App\Http\Controllers\Api\PostNummerQueueController;
use App\Http\Controllers\Api\RatsitDataController;
use App\Http\Controllers\Api\RatsitForetagQueueController;
use App\Http\Controllers\Api\RatsitQueueController;
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
// Alias routes for personeel-specific lookups
Route::apiResource('hitta-personer-data', HittaDataController::class);
Route::post('/hitta-personer-data/bulk', [HittaDataController::class, 'bulkStore']);

Route::apiResource('eniro-data', EniroDataController::class);
Route::post('/eniro-data/bulk', [EniroDataController::class, 'bulkStore']);

Route::apiResource('upplysning-data', UpplysningDataController::class);
Route::post('/upplysning-data/bulk', [UpplysningDataController::class, 'bulkStore']);

// Ratsit data - requires authentication (private/scraped data)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('ratsit-data', RatsitDataController::class);
});
Route::post('/ratsit-data/bulk', [RatsitDataController::class, 'bulkStore']);
// Alias route for person-level ratsit
Route::apiResource('ratsit-personer-data', RatsitDataController::class);
Route::post('/ratsit-personer-data/bulk', [RatsitDataController::class, 'bulkStore']);

// Data private - requires authentication for individual operations but bulk is public for scraping
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('data-private', DataPrivateController::class);
});
Route::post('/data-private/bulk', [DataPrivateController::class, 'bulkStore']);

// Merinfo Queue REST + helpers
Route::get('/merinfo-queue', [MerinfoQueueController::class, 'index']);
Route::get('/merinfo-queue/{id}', [MerinfoQueueController::class, 'show'])->whereNumber('id');
Route::get('/merinfo-queue/run-personer', [MerinfoQueueController::class, 'runPersoner']);
Route::post('/merinfo-queue/bulk-update', [MerinfoQueueController::class, 'bulkUpdate']);
Route::put('/merinfo-queue/update/{postNummer}', [MerinfoQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');
// Merinfo companies (företag) queue endpoints
Route::get('/merinfo-foretag-queue', [MerinfoForetagQueueController::class, 'index']);
Route::get('/merinfo-foretag-queue/{id}', [MerinfoForetagQueueController::class, 'show'])->whereNumber('id');
Route::get('/merinfo-foretag-queue/run-foretag', [MerinfoForetagQueueController::class, 'runForetag']);
Route::post('/merinfo-foretag-queue/bulk-update', [MerinfoForetagQueueController::class, 'bulkUpdate']);
Route::put('/merinfo-foretag-queue/update/{postNummer}', [MerinfoForetagQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// Hitta companies (företag) queue endpoints
Route::get('/hitta-foretag-queue', [HittaForetagQueueController::class, 'index']);
Route::get('/hitta-foretag-queue/{id}', [HittaForetagQueueController::class, 'show'])->whereNumber('id');
Route::get('/hitta-foretag-queue/run-foretag', [HittaForetagQueueController::class, 'runForetag']);
Route::post('/hitta-foretag-queue/bulk-update', [HittaForetagQueueController::class, 'bulkUpdate']);
Route::put('/hitta-foretag-queue/update/{postNummer}', [HittaForetagQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// Ratsit companies (företag) queue endpoints
Route::get('/ratsit-foretag-queue', [RatsitForetagQueueController::class, 'index']);
Route::get('/ratsit-foretag-queue/{id}', [RatsitForetagQueueController::class, 'show'])->whereNumber('id');
Route::get('/ratsit-foretag-queue/run-foretag', [RatsitForetagQueueController::class, 'runForetag']);
Route::post('/ratsit-foretag-queue/bulk-update', [RatsitForetagQueueController::class, 'bulkUpdate']);
Route::put('/ratsit-foretag-queue/update/{postNummer}', [RatsitForetagQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// Postnummer foretag queue endpoints
Route::get('/postnummer-foretag-queue', [PostNummerForetagQueueController::class, 'index']);
Route::get('/postnummer-foretag-queue/{id}', [PostNummerForetagQueueController::class, 'show'])->whereNumber('id');
Route::get('/postnummer-foretag-queue/run-foretag', [PostNummerForetagQueueController::class, 'runForetag']);
Route::post('/postnummer-foretag-queue/bulk-update', [PostNummerForetagQueueController::class, 'bulkUpdate']);
Route::put('/postnummer-foretag-queue/update/{postNummer}', [PostNummerForetagQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

Route::apiResource('post-nummer', PostNummerApiController::class);
Route::put('/post-nummer/by-code/{postnummer}', [PostNummerApiController::class, 'updateByPostnummer']);
Route::post('/post-nummer/bulk-update', [PostNummerApiController::class, 'bulkUpdateByPostnummer']);
Route::post('/post-nummer/bulk-update-totals', [PostNummerApiController::class, 'bulkUpdateTotals']);
Route::post('/post-nummer/increment-counters/{postnummer}', [PostNummerApiController::class, 'incrementCounters']);
Route::get('/post-nummer/resume-info/{postnummer}', [PostNummerApiController::class, 'getResumeInfo']);
Route::post('/post-nummer/reset-counters/{postnummer}', [PostNummerApiController::class, 'resetCounters']);

Route::get('/postnummer-que/first', [PostNummerQueController::class, 'getFirstPostNummer']);
Route::post('/postnummer-que/first-next', [PostNummerQueController::class, 'firstNext']);
Route::post('/postnummer-que/process-next', [PostNummerQueController::class, 'processNext']);
Route::apiResource('postnummer-que', PostNummerQueController::class)->only(['update']);
Route::put('/postnummer-que/by-code/{postNummer}', [PostNummerQueController::class, 'updateByPostNummer']);
Route::post('/postnummer-que/bulk-update', [PostNummerQueController::class, 'bulkUpdate']);

Route::post('/hitta-se', [HittaSeController::class, 'store']);
Route::post('/hitta-se/batch', [HittaSeController::class, 'batchStore']);

// Hitta Queue API routes
Route::get('/hitta-queue/run-personer', [HittaQueueController::class, 'runPersoner']);
Route::post('/hitta-queue/bulk-update', [HittaQueueController::class, 'bulkUpdate']);
Route::put('/hitta-queue/update/{postNummer}', [HittaQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// Ratsit Queue API routes
Route::get('/ratsit-queue/run-personer', [RatsitQueueController::class, 'runPersoner']);
Route::post('/ratsit-queue/bulk-update', [RatsitQueueController::class, 'bulkUpdate']);
Route::put('/ratsit-queue/update/{postNummer}', [RatsitQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// PostNummer Queue API routes (comprehensive queue tracker)
Route::post('/postnummer-queue/bulk-update', [PostNummerQueueController::class, 'bulkUpdate']);
Route::put('/postnummer-queue/update/{postNummer}', [PostNummerQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');
