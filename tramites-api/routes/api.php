<?php

use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\TramiteController;
use Illuminate\Support\Facades\Route;

Route::apiResource('instituciones', InstitucionController::class)
    ->only(['index', 'store']);

Route::apiResource('tramites', TramiteController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy']);
