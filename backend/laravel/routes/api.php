<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Users\AuthFirebaseController;
use App\Http\Controllers\Portal\AuthPortalController;

Route::prefix('api/auth/firebase')->group(function () {
    Route::post('/check-email', [AuthFirebaseController::class, 'checkEmail']);
    Route::post('/register', [AuthFirebaseController::class, 'register']);
    Route::post('/login', [AuthFirebaseController::class, 'login']);
    Route::post('/complete-profile', [AuthFirebaseController::class, 'completeProfile']);
    Route::post('/update-location', [AuthFirebaseController::class, 'updateLocation']);
    Route::post('/logout', [AuthFirebaseController::class, 'logout']);
    Route::get('/profile/{user_refid}', [AuthFirebaseController::class, 'getProfile']);
});
Route::prefix('api/portal/auth')->group(function () {
    Route::post('/login', [AuthPortalController::class, 'login']);
    Route::post('/admin/login', [AuthPortalController::class, 'adminLogin']);
    Route::post('/staff/login', [AuthPortalController::class, 'staffLogin']);
    Route::post('/operator/login', [AuthPortalController::class, 'operatorLogin']);
    Route::post('/logout', [AuthPortalController::class, 'logout']);
    Route::post('/verify-session', [AuthPortalController::class, 'verifySession']);
});
