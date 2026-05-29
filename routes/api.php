<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\SiteSettingController;
use Illuminate\Support\Facades\Route;

// Auth pública
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Recursos y categorías (lectura pública)
Route::get('/categories',              [CategoryController::class, 'index']);
Route::get('/categories/{category}',   [CategoryController::class, 'show']);
Route::get('/resources',               [ResourceController::class, 'index']);
Route::get('/resources/{resource}',    [ResourceController::class, 'show']);
Route::get('/resources/{resource}/availability', [ResourceController::class, 'availability']);

// Ajustes públicos del sitio (solo lectura)
Route::get('/settings',         [SiteSettingController::class, 'index']);
Route::get('/settings/{group}', [SiteSettingController::class, 'show']);

// Rutas autenticadas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Reservas
    Route::get('/reservations/calendar', [ReservationController::class, 'calendar']);
    Route::apiResource('reservations', ReservationController::class);

    // Admin y gestor
    Route::middleware('role:admin|manager')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('resources',  ResourceController::class)->except(['index', 'show']);

        Route::get('/admin/dashboard',                        [AdminController::class, 'dashboard']);
        Route::get('/admin/users',                            [AdminController::class, 'users']);
        Route::put('/admin/users/{user}',                     [AdminController::class, 'updateUser']);
        Route::delete('/admin/users/{user}',                  [AdminController::class, 'deleteUser']);
        Route::get('/admin/reservations/{reservation}/logs',  [AdminController::class, 'reservationLogs']);
    });

    // Solo admin
    Route::middleware('role:admin')->group(function () {
        Route::post('/settings', [SiteSettingController::class, 'update']);
    });
});
