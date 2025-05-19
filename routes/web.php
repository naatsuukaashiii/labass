<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\InfoController;

Route::get('/info/server', [InfoController::class, 'serverInfo']);
Route::get('/info/client', [InfoController::class, 'clientInfo']);
Route::get('/info/database', [InfoController::class, 'databaseInfo']);