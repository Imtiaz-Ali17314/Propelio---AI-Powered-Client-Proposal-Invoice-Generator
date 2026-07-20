<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('clients', ClientController::class);

    Route::apiResource('proposals', ProposalController::class)->except(['update']);
    Route::put('proposals/{proposal}', [ProposalController::class, 'update']);

    Route::post('proposals/{proposal}/generate-scope', [ProposalController::class, 'generateScope']);
    Route::post('proposals/{proposal}/generate-timeline', [ProposalController::class, 'generateTimeline']);
    Route::post('proposals/{proposal}/generate-cost', [ProposalController::class, 'generateCost']);

    Route::apiResource('invoices', InvoiceController::class);
    Route::post('proposals/{proposal}/convert-to-invoice', [InvoiceController::class, 'storeFromProposal']);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf']);

    Route::get('invoices/{invoice}/payments', [PaymentController::class, 'index']);
    Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store']);
    Route::delete('invoices/{invoice}/payments/{payment}', [PaymentController::class, 'destroy']);
});