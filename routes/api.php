<?php

use Illuminate\Support\Facades\Route;
use App\Models\FailedJob;

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

// Status endpoint
Route::get('/status', function() {
    return response()->json([
        'statusCode' => 200,
        'message' => 'OK',
        'details' => [
            'queue_failures' => FailedJob::count(),
        ],
    ], 200);
});
