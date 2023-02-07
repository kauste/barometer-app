<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarometerController;

Route::get('/', [BarometerController::class, 'index']);
