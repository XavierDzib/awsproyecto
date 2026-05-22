<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\ProfesorController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas API SIN protección CSRF
Route::withoutMiddleware(['web', \App\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        
        // Alumnos
        Route::get('/alumnos', [AlumnoController::class, 'index']);
        Route::get('/alumnos/{id}', [AlumnoController::class, 'show']);
        Route::post('/alumnos', [AlumnoController::class, 'store']);
        Route::put('/alumnos/{id}', [AlumnoController::class, 'update']);
        Route::delete('/alumnos/{id}', [AlumnoController::class, 'destroy']);
        Route::post('/alumnos/{id}/fotoPerfil', [AlumnoController::class, 'uploadFotoPerfil']);
        Route::post('/alumnos/{id}/session/login', [AlumnoController::class, 'login']);
        Route::post('/alumnos/{id}/session/verify', [AlumnoController::class, 'verifySession']);
        Route::post('/alumnos/{id}/session/logout', [AlumnoController::class, 'logout']);
        Route::post('/alumnos/{id}/email', [AlumnoController::class, 'sendEmail']);
        
        // Profesores
        Route::get('/profesores', [ProfesorController::class, 'index']);
        Route::get('/profesores/{id}', [ProfesorController::class, 'show']);
        Route::post('/profesores', [ProfesorController::class, 'store']);
        Route::put('/profesores/{id}', [ProfesorController::class, 'update']);
        Route::delete('/profesores/{id}', [ProfesorController::class, 'destroy']);
    });