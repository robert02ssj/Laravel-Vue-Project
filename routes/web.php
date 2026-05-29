<?php

use Illuminate\Support\Facades\Route;

// Todas las rutas sirven la SPA; Vue Router gestiona la navegación en cliente
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
