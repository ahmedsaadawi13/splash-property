// FILE: /config/config.php
<?php

/**
 * Main Configuration File
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */

return [
    'app' => [
        'name' => 'SplashProperty',
        'version' => '1.0.0',
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => getenv('APP_DEBUG') === 'true',
        'timezone' => 'UTC'
    ],

    'mail' => [
        'from' => getenv('MAIL_FROM') ?: 'noreply@splashproperty.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'SplashProperty',
        'host' => getenv('MAIL_HOST') ?: 'localhost',
        'port' => getenv('MAIL_PORT') ?: 587,
        'username' => getenv('MAIL_USERNAME') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: ''
    ],

    'upload' => [
        'max_size' => 10485760, // 10MB
        'allowed_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]
    ],

    'pagination' => [
        'per_page' => 20
    ],

    'currency' => [
        'default' => 'SAR',
        'supported' => ['SAR', 'USD', 'EUR', 'GBP', 'AED', 'EGP']
    ]
];
