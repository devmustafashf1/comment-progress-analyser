<?php

use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskAnalysisController;

// Your main API route
Route::post('/analyze-task', [TaskAnalysisController::class, 'analyze']);

// Simple test route
Route::post('/test-post', function (Request $request) {
    // Just return what was sent in the request
    return response()->json([
        'received' => $request->all()
    ]);
});
