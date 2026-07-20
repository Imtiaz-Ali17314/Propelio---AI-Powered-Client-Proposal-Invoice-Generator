<?php

return [
   'paths' => ['api/*', 'sanctum/csrf-cookie', 'register', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:8000', 'http://127.0.0.1:8000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // MUST be true for Sanctum SPA cookie-based auth to work
    'supports_credentials' => true,
];