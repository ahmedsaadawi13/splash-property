// FILE: /app/core/Request.php
<?php

/**
 * Request Class
 * Handles HTTP request data
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class Request
{
    /**
     * Get request method
     */
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if request method is
     */
    public function isMethod($method)
    {
        return $this->method() === strtoupper($method);
    }

    /**
     * Check if request is GET
     */
    public function isGet()
    {
        return $this->isMethod('GET');
    }

    /**
     * Check if request is POST
     */
    public function isPost()
    {
        return $this->isMethod('POST');
    }

    /**
     * Get all input data
     */
    public function all()
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * Get input value
     */
    public function input($key, $default = null)
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Get only specified keys
     */
    public function only($keys)
    {
        $data = $this->all();
        $result = [];

        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $result[$key] = $data[$key];
            }
        }

        return $result;
    }

    /**
     * Check if input has key
     */
    public function has($key)
    {
        return isset($this->all()[$key]);
    }

    /**
     * Get query parameter
     */
    public function query($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get POST parameter
     */
    public function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get uploaded file
     */
    public function file($key)
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Get all uploaded files
     */
    public function files()
    {
        return $_FILES;
    }

    /**
     * Get request URI
     */
    public function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Get request path
     */
    public function path()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Get header
     */
    public function header($key, $default = null)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$key] ?? $default;
    }

    /**
     * Get client IP
     */
    public function ip()
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get user agent
     */
    public function userAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get JSON body
     */
    public function json()
    {
        $body = file_get_contents('php://input');
        return json_decode($body, true);
    }
}
