<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.manager.auth.login');
});

Route::get('/login', function () {
    return redirect()->route('filament.manager.auth.login');
})->name('login');
