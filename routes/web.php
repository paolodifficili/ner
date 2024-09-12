<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('manager');
});


Route::get('/upload', function () {
    return view('upload');
});