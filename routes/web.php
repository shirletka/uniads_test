<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

Route::get('/', [UploadController::class, 'index']);
Route::get('/file/{token}', [UploadController::class, 'show']);
Route::get('/delete/{deleteToken}', [UploadController::class, 'destroy']);

// API маршрут без CSRF защиты
Route::post('/', [UploadController::class, 'store'])->withoutMiddleware(['web']);
