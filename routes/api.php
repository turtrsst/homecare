<?php

use App\Http\Controllers\Api\V1 as Api;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — untuk aplikasi mobile / klien eksternal
|--------------------------------------------------------------------------
| Envelope konsisten (App\Support\ApiResponse), autentikasi Sanctum,
| rate limiting per grup. Jangan menambah endpoint di luar prefix v1.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Publik
    Route::post('/auth/register', [Api\AuthController::class, 'register'])
        ->middleware('throttle:6,1');
    Route::post('/auth/login', [Api\AuthController::class, 'login'])
        ->middleware('throttle:6,1');
    Route::get('/services', [Api\ServiceController::class, 'index']);
    Route::get('/services/{service:slug}', [Api\ServiceController::class, 'show']);

    // Terautentikasi (Sanctum Bearer token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [Api\AuthController::class, 'logout']);
        Route::get('/auth/me', [Api\AuthController::class, 'me']);

        Route::get('/patients', [Api\PatientController::class, 'index']);
        Route::post('/patients', [Api\PatientController::class, 'store']);
        Route::get('/patients/{patient}', [Api\PatientController::class, 'show']);
        Route::put('/patients/{patient}', [Api\PatientController::class, 'update']);
        Route::get('/patients/{patient}/addresses', [Api\AddressController::class, 'indexForPatient']);
        Route::post('/patients/{patient}/addresses', [Api\AddressController::class, 'storeForPatient']);
        Route::put('/addresses/{address}', [Api\AddressController::class, 'update']);
        Route::get('/addresses', [Api\AddressController::class, 'index']);

        Route::get('/homecare', [Api\HomecareController::class, 'index']);
        Route::post('/homecare', [Api\HomecareController::class, 'store'])->middleware('throttle:10,1');
        Route::get('/homecare/{request:code}', [Api\HomecareController::class, 'show']);
        Route::get('/homecare/{request:code}/status', [Api\HomecareController::class, 'status']);
        Route::get('/homecare/{request:code}/appointment', [Api\HomecareController::class, 'appointment']);
        Route::post('/homecare/{request:code}/cancel', [Api\HomecareController::class, 'cancel']);
        Route::post('/homecare/{request:code}/information', [Api\HomecareController::class, 'provideInformation']);

        Route::get('/notifications', [Api\NotificationController::class, 'index']);
        Route::post('/notifications/{notification}/read', [Api\NotificationController::class, 'markRead']);
    });
});
