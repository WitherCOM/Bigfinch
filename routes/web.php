<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/gocardless/callback', [\App\Http\Controllers\GocardlessController::class,'callback'])->name('gocardless.callback');
