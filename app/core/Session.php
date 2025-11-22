// FILE: /app/core/Session.php
<?php

/**
 * Session Class
 * Handles session management
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session configuration
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);

            session_start();
        }
    }

    /**
     * Set session value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Flash message (set for one request)
     */
    public function flash($key, $value)
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get flash message and remove
     */
    public function getFlash($key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Regenerate session ID
     */
    public function regenerate()
    {
        session_regenerate_id(true);
    }

    /**
     * Destroy session
     */
    public function destroy()
    {
        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();
    }

    /**
     * Get all session data
     */
    public function all()
    {
        return $_SESSION;
    }
}
