<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AngularController;


Route::any('/{any}', [AngularController::class, 'index'])->where('any', '^(?!api).*$');
