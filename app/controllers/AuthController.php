// FILE: /app/controllers/AuthController.php
<?php

/**
 * Auth Controller
 * Handles authentication
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function login()
    {
        // If already authenticated, redirect to dashboard
        if ($this->auth->check()) {
            return $this->redirect('/dashboard');
        }

        return $this->render('auth/login', [], null);
    }

    /**
     * Process login
     */
    public function processLogin()
    {
        $this->verifyCsrf();

        $email = $this->request->post('email');
        $password = $this->request->post('password');

        if ($this->auth->attempt($email, $password)) {
            $user = $this->auth->user();

            // Log login activity
            $activityLog = new ActivityLogModel();
            $activityLog->log($user['tenant_id'], $user['id'], 'user', $user['id'], 'login', 'User logged in');

            return $this->redirect('/dashboard');
        }

        $this->session->flash('error', 'Invalid email or password');
        return $this->redirect('/login');
    }

    /**
     * Logout
     */
    public function logout()
    {
        $user = $this->auth->user();

        if ($user) {
            // Log logout activity
            $activityLog = new ActivityLogModel();
            $activityLog->log($user['tenant_id'], $user['id'], 'user', $user['id'], 'logout', 'User logged out');
        }

        $this->auth->logout();
        return $this->redirect('/login');
    }
}
