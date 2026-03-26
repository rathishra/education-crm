<?php
/**
 * Application Configuration
 */
return [
    'name'     => env('APP_NAME', 'Education CRM'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost/crm'),
    'timezone' => 'Asia/Kolkata',
    'locale'   => 'en',

    // Session
    'session' => [
        'name'     => 'educrm_session',
        'lifetime' => 120, // minutes
        'path'     => '/',
        'secure'   => false,
        'httponly'  => true,
    ],

    // Pagination
    'per_page' => 15,

    // Upload limits
    'max_upload_size' => 5 * 1024 * 1024, // 5MB
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
];
