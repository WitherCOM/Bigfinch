<?php

use App\Filament\Pages\ConfirmPassword;
use App\Http\Controllers\GocardlessController;
use Illuminate\Support\Facades\Route;

Route::get('/gocardless/callback', [GocardlessController::class, 'callback'])->name('gocardless.callback');

Route::get('/password/confirm', fn () => redirect(ConfirmPassword::getUrl()))->name('password.confirm');
