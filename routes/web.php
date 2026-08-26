<?php

use App\Http\Controllers\AnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnalysisController::class, 'show']);
Route::post('/analyze', [AnalysisController::class, 'analyze']);