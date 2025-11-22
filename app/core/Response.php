// FILE: /app/core/Response.php
<?php

/**
 * Response Class
 * Handles HTTP responses
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class Response
{
    /**
     * Redirect to URL
     */
    public function redirect($url, $statusCode = 302)
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    /**
     * Return JSON response
     */
    public function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Set status code
     */
    public function setStatusCode($code)
    {
        http_response_code($code);
        return $this;
    }

    /**
     * Set header
     */
    public function setHeader($key, $value)
    {
        header("{$key}: {$value}");
        return $this;
    }

    /**
     * Download file
     */
    public function download($filePath, $filename = null)
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die("File not found");
        }

        $filename = $filename ?: basename($filePath);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        readfile($filePath);
        exit;
    }

    /**
     * Send file
     */
    public function file($filePath, $mimeType = null)
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die("File not found");
        }

        if (!$mimeType) {
            $mimeType = mime_content_type($filePath);
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }
}
