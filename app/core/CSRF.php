// FILE: /app/core/CSRF.php
<?php

/**
 * CSRF Class
 * Handles CSRF token generation and validation
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class CSRF
{
    /**
     * Generate CSRF token
     */
    public static function generate()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Get CSRF token
     */
    public static function token()
    {
        return self::generate();
    }

    /**
     * Verify CSRF token
     */
    public static function verify()
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$token || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate CSRF field for forms
     */
    public static function field()
    {
        $token = self::token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Generate CSRF meta tag
     */
    public static function meta()
    {
        $token = self::token();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
}
