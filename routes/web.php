<?php

use Illuminate\Support\Facades\Route;

Route::get('/gocardless/callback', [\App\Http\Controllers\GocardlessController::class,'callback'])->name('gocardless.callback');
