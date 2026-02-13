<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware('auth')->post('/likes/toggle', [LikeApiController::class, 'toggle']);
