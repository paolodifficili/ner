<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('mgr');
});


Route::get('/upload', function () {
    return view('upload');
});