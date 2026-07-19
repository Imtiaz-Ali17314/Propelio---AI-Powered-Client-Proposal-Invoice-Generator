<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require valid Sanctum session)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('clients', ClientController::class);

    Route::apiResource('proposals', ProposalController::class)->except(['update']);
    Route::put('proposals/{proposal}', [ProposalController::class, 'update']);

    Route::post('proposals/{proposal}/generate-scope', [ProposalController::class, 'generateScope']);
    Route::post('proposals/{proposal}/generate-timeline', [ProposalController::class, 'generateTimeline']);
    Route::post('proposals/{proposal}/generate-cost', [ProposalController::class, 'generateCost']);

    // Phase 3 onwards: invoices routes will go here
    // Route::apiResource('invoices', InvoiceController::class);
});