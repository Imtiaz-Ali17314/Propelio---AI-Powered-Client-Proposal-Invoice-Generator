<?php

use Illuminate\Support\Facades\Route;

// Catch-all: any non-API route serves the Vue SPA shell.
// Vue Router then takes over and handles client-side routing.
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');