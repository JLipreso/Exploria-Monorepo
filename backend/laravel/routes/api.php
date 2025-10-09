<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Users\AuthFirebaseController;

Route::prefix('api/auth/firebase')->group(function () {
    Route::post('/check-email', [AuthFirebaseController::class, 'checkEmail']);
    Route::post('/register', [AuthFirebaseController::class, 'register']);
    Route::post('/login', [AuthFirebaseController::class, 'login']);
    Route::post('/complete-profile', [AuthFirebaseController::class, 'completeProfile']);
    Route::post('/update-location', [AuthFirebaseController::class, 'updateLocation']);
    Route::post('/logout', [AuthFirebaseController::class, 'logout']);
    Route::get('/profile/{user_refid}', [AuthFirebaseController::class, 'getProfile']);
});
