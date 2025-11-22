// FILE: /app/core/Controller.php
<?php

/**
 * Base Controller Class
 * All controllers extend this class
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class Controller
{
    protected $view;
    protected $auth;
    protected $request;
    protected $response;
    protected $session;

    public function __construct()
    {
        $this->view = new View();
        $this->auth = new Auth();
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
    }

    /**
     * Render view
     */
    protected function render($viewPath, $data = [], $layout = 'main')
    {
        return $this->view->render($viewPath, $data, $layout);
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url, $statusCode = 302)
    {
        return $this->response->redirect($url, $statusCode);
    }

    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        return $this->response->json($data, $statusCode);
    }

    /**
     * Check if user is authenticated
     */
    protected function requireAuth()
    {
        if (!$this->auth->check()) {
            $this->redirect('/login');
            exit;
        }
    }

    /**
     * Check if user has role
     */
    protected function requireRole($roles)
    {
        $this->requireAuth();

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!$this->auth->hasRole($roles)) {
            http_response_code(403);
            die("Access Denied: Insufficient permissions");
        }
    }

    /**
     * Get current authenticated user
     */
    protected function getUser()
    {
        return $this->auth->user();
    }

    /**
     * Get current tenant ID
     */
    protected function getTenantId()
    {
        $user = $this->getUser();
        return $user ? $user['tenant_id'] : null;
    }

    /**
     * Verify CSRF token
     */
    protected function verifyCsrf()
    {
        if (!CSRF::verify()) {
            http_response_code(403);
            die("CSRF token validation failed");
        }
    }
}
