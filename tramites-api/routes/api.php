<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\TramiteController;
use Illuminate\Support\Facades\Route;

// ── Autenticación ─────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout',  [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me',       [AuthController::class, 'me']);
    });
});

// ── Rutas públicas (solo lectura) ─────────────────────────────────────────────
Route::get('instituciones',      [InstitucionController::class, 'index']);
Route::get('tramites',           [TramiteController::class, 'index']);
Route::get('tramites/{tramite}', [TramiteController::class, 'show']);

// ── Rutas protegidas (escritura) ──────────────────────────────────────────────
Route::middleware('auth:api')->group(function () {
    Route::post('instituciones', [InstitucionController::class, 'store']);

    Route::post('tramites',                          [TramiteController::class, 'store']);
    Route::put('tramites/{tramite}',                 [TramiteController::class, 'update']);
    Route::delete('tramites/{tramite}',              [TramiteController::class, 'destroy']);
    Route::post('tramites/{tramite}/resumen-ia',     [TramiteController::class, 'resumen']);
});
