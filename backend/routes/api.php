<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\UserManagementController;

Route::get('/sanctum/csrf-cookie', function (Request $request) {
    return response()->json(['message' => 'CSRF cookie set']);
})->middleware('web'); // Important: use 'web' middleware, not 'api'

Route::post('/login', [AuthenticatedSessionController::class, 'login']);
Route::put('/changepassword', [AuthenticatedSessionController::class, 'change']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'logout']);
    Route::get('/user', [AuthenticatedSessionController::class, 'getUserData']);
    //usermanagement controller 
    Route::get('/roles', [UserManagementController::class, 'getRoles']);
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::post('/users', [UserManagementController::class, 'store']);
    Route::get('/users/{id}', [UserManagementController::class, 'show']);
    Route::put('/users/{id}', [UserManagementController::class, 'update']);
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
    Route::patch('/users/{id}/toggle-status', [UserManagementController::class, 'toggleStatus']);
});