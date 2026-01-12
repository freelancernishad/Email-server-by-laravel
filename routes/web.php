<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('config-manager');
});

Route::get('/config-manager', function () {
    return view('config-manager');
});
