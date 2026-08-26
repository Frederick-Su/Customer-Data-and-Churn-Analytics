<?php

use App\Http\Controllers\AnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnalysisController::class, 'index']);

// Customer Analytics
Route::get('/customer-analysis', [AnalysisController::class, 'show']);
Route::post('/analyze', [AnalysisController::class, 'analyze']);

// Ticketing Analytics
Route::get('/ticket-analysis', [AnalysisController::class, 'showTicketing']);
Route::post('/analyze-tickets', [AnalysisController::class, 'analyzeTicketing']);