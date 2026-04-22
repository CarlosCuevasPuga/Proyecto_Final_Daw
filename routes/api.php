<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CuponController;
use App\Http\Controllers\Api\RestauranteController;
use App\Http\Controllers\Api\RutaController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – PAPM (Passport Pal Motril)
|--------------------------------------------------------------------------
|
| Prefijo base: /api  (definido en bootstrap/app.php)
| Autenticación: Laravel Sanctum (token Bearer)
|
*/

// ── Auth (público) ────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('registro', [AuthController::class, 'registro']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me',     [AuthController::class, 'me']);
        Route::post('logout',[AuthController::class, 'logout']);
    });
});

// ── Restaurantes (lectura pública / escritura privada) ────────
Route::prefix('restaurantes')->group(function () {
    Route::get('/',    [RestauranteController::class, 'index']);
    Route::get('{restaurante}', [RestauranteController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('{restaurante}/valorar', [RestauranteController::class, 'valorar']);
    });
});

// ── Rutas gastronómicas ───────────────────────────────────────
Route::prefix('rutas')->group(function () {
    Route::get('/',    [RutaController::class, 'index']);

    // IMPORTANTE: 'progreso' antes de '{ruta}' para evitar conflictos
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('progreso', [RutaController::class, 'progreso']);
    });

    Route::get('{ruta}',                [RutaController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('{ruta}/iniciar',   [RutaController::class, 'iniciar']);
        Route::post('{ruta}/completar', [RutaController::class, 'completar']);
    });
});

// ── Cupones ───────────────────────────────────────────────────
Route::prefix('cupones')->middleware('auth:sanctum')->group(function () {
    Route::get('/',             [CuponController::class, 'index']);
    Route::get('mis-cupones',   [CuponController::class, 'misCupones']);
    Route::post('{cupon}/canjear', [CuponController::class, 'canjear']);
});

// ── Usuarios ──────────────────────────────────────────────────
Route::prefix('usuarios')->middleware('auth:sanctum')->group(function () {
    Route::get('perfil',        [UserController::class, 'perfil']);
    Route::put('perfil',        [UserController::class, 'actualizarPerfil']);
    Route::get('ranking',       [UserController::class, 'ranking']);
    Route::post('suscripcion',  [UserController::class, 'suscribirse']);
});

// ── Fallback ──────────────────────────────────────────────────
Route::fallback(fn() => response()->json(['error' => 'Ruta de API no encontrada.'], 404));
