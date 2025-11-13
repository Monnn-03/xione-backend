<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CORS (Cross-Origin Resource Sharing) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations (like
    | JavaScript requests) can be executed on your application.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
			'http://localhost:5173',
      'http://localhost:5173/',
		], // <-- KITA AKAN UBAH INI

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];