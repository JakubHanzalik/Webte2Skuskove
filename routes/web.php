<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AngularController;


Route::get('/', function () {
    return view('welcome');
});
//Route::any('/{any}', [AngularController::class, 'index'])->where('any', '^(?!api).*$');
