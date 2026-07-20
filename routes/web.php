<?php

use Illuminate\Support\Facades\Route;

// ✅ 1. CSRF Cookie Route - Sab se pehle
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
})->middleware('web');

// ✅ 2. API Routes ko include karo (BEST TAREEQA - same as Laravel default)
Route::prefix('api')->group(function () {
    require base_path('routes/api.php');
});

// ✅ 3. Catch-all route - SAB SE NECHEY
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');