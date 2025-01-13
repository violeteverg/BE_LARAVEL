<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::apiResource('customer',CustomerController::class);
Route::apiResource('product',ProductController::class);
Route::apiResource('transaction',TransactionController::class);