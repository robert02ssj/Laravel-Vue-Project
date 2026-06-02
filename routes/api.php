<?php

// Importamos los controladores que vamos a usar
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\SiteSettingController;
use Illuminate\Support\Facades\Route;

// ===========================================================
// RUTAS PÚBLICAS — cualquiera puede acceder sin estar logueado
// ===========================================================

// Registro e inicio de sesión
Route::post('/register', [AuthController::class, 'register']); // Crear cuenta nueva
Route::post('/login',    [AuthController::class, 'login']);    // Iniciar sesión

// Ver categorías y recursos (cualquiera puede verlos)
Route::get('/categories',                        [CategoryController::class, 'index']);        // Listar todas las categorías
Route::get('/categories/{category}',             [CategoryController::class, 'show']);         // Ver una categoría concreta
Route::get('/resources',                         [ResourceController::class, 'index']);        // Listar todos los recursos
Route::get('/resources/{resource}',              [ResourceController::class, 'show']);         // Ver un recurso concreto
Route::get('/resources/{resource}/availability', [ResourceController::class, 'availability']); // Ver huecos libres de un recurso

// Configuración del sitio (solo lectura pública)
Route::get('/settings',         [SiteSettingController::class, 'index']); // Ver todos los ajustes
Route::get('/settings/{group}', [SiteSettingController::class, 'show']);  // Ver ajustes de un grupo


// ===========================================================
// RUTAS PRIVADAS — solo para usuarios que han iniciado sesión
// El middleware 'auth:sanctum' comprueba el token en la cabecera
// ===========================================================
Route::middleware('auth:sanctum')->group(function () {

    // Cerrar sesión y ver los datos del usuario actual
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Reservas (el usuario solo ve las suyas; admin/gestor ven todas)
    Route::get('/reservations/calendar', [ReservationController::class, 'calendar']); // Eventos para el calendario
    Route::apiResource('reservations', ReservationController::class);
    // apiResource genera automáticamente estas rutas:
    //   GET    /reservations          → index()   (listar)
    //   POST   /reservations          → store()   (crear)
    //   GET    /reservations/{id}     → show()    (ver una)
    //   PUT    /reservations/{id}     → update()  (actualizar)
    //   DELETE /reservations/{id}     → destroy() (eliminar)


    // -------------------------------------------------------
    // RUTAS SOLO PARA ADMINISTRADORES Y GESTORES
    // El middleware 'role:admin|manager' lo comprueba Spatie Permission
    // -------------------------------------------------------
    Route::middleware('role:admin|manager')->group(function () {

        // Gestión de categorías y recursos (crear, editar, borrar)
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('resources',  ResourceController::class)->except(['index', 'show']);

        // Panel de administración
        Route::get('/admin/dashboard',                       [AdminController::class, 'dashboard']);        // Estadísticas generales
        Route::get('/admin/users',                           [AdminController::class, 'users']);            // Listar usuarios
        Route::put('/admin/users/{user}',                    [AdminController::class, 'updateUser']);       // Editar un usuario
        Route::delete('/admin/users/{user}',                 [AdminController::class, 'deleteUser']);       // Borrar un usuario
        Route::get('/admin/reservations/{reservation}/logs', [AdminController::class, 'reservationLogs']); // Ver historial de una reserva
    });

    // -------------------------------------------------------
    // RUTAS SOLO PARA ADMINISTRADORES
    // -------------------------------------------------------
    Route::middleware('role:admin')->group(function () {
        Route::post('/settings', [SiteSettingController::class, 'update']); // Guardar configuración del sitio
    });
});
