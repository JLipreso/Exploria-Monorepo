<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\AuthFirebaseController;

/*
|--------------------------------------------------------------------------
| Firebase Authentication API Routes
|--------------------------------------------------------------------------
|
| Routes for Firebase authentication functionality
| Prefix: /api/auth/firebase
|
*/

Route::prefix('api/auth/firebase')->group(function () {
    
    // Public routes (no authentication required)
    Route::post('/check-email', [AuthFirebaseController::class, 'checkEmail']);
    Route::post('/register', [AuthFirebaseController::class, 'register']);
    Route::post('/login', [AuthFirebaseController::class, 'login']);
    
    // Protected routes (require authentication)
    // Add your authentication middleware here when implemented
    // Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/complete-profile', [AuthFirebaseController::class, 'completeProfile']);
        Route::post('/update-location', [AuthFirebaseController::class, 'updateLocation']);
        Route::post('/logout', [AuthFirebaseController::class, 'logout']);
        Route::get('/profile/{user_refid}', [AuthFirebaseController::class, 'getProfile']);
    // });
});

/*
|--------------------------------------------------------------------------
| Alternative Route Definitions (Individual)
|--------------------------------------------------------------------------
|
| If you prefer individual route definitions instead of grouping:
|
| Route::post('/api/auth/firebase/check-email', [AuthFirebaseController::class, 'checkEmail']);
| Route::post('/api/auth/firebase/register', [AuthFirebaseController::class, 'register']);
| Route::post('/api/auth/firebase/login', [AuthFirebaseController::class, 'login']);
| Route::post('/api/auth/firebase/complete-profile', [AuthFirebaseController::class, 'completeProfile']);
| Route::post('/api/auth/firebase/update-location', [AuthFirebaseController::class, 'updateLocation']);
| Route::post('/api/auth/firebase/logout', [AuthFirebaseController::class, 'logout']);
| Route::get('/api/auth/firebase/profile/{user_refid}', [AuthFirebaseController::class, 'getProfile']);
|
*/
