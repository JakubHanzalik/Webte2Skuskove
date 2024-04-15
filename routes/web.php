<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AngularController;
use App\Http\Controllers\AuthController;


Route::any('/{any}', [AngularController::class, 'index'])->where('any', '^(?!api).*$');
Route::any('/api/login', [AuthController::class, 'index']);