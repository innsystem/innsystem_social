<?php

use App\Http\Controllers\Api\OpenCartIntegrationController;
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

Route::prefix('v1/opencart')->middleware('opencart.api')->group(function () {
    Route::get('/connection-status', [OpenCartIntegrationController::class, 'connectionStatus']);
    Route::post('/oauth-url', [OpenCartIntegrationController::class, 'oauthUrl']);
    Route::post('/publish', [OpenCartIntegrationController::class, 'publish']);
});
