<?php

use App\Http\Controllers\StreamController;
use App\Http\Controllers\VidioController;
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

Route::get('/stream/vidio/indosiar', [StreamController::class, 'indosiar']);
Route::get('/stream/cubmu/token', [StreamController::class, 'getToken']);
Route::post('/stream/cubmu/license', [StreamController::class, 'getLicenseDRMToday']);

Route::get('/stream/vidio/check', [VidioController::class, 'index']);