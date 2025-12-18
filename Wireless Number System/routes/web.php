<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to(config('nova.path') . '/login');
});
Route::get('/login', function () {
    return redirect()->to(config('nova.path') . '/login');
})
->name('login');
