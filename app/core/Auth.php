// FILE: /app/core/Auth.php
<?php

/**
 * Auth Class
 * Handles user authentication and authorization
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class Auth
{
    private $session;
    private $userModel;

    public function __construct()
    {
        $this->session = new Session();

        // Lazy load user model to avoid circular dependencies
        if (!$this->userModel && class_exists('UserModel')) {
            $this->userModel = new UserModel();
        }
    }

    /**
     * Attempt login
     */
    public function attempt($email, $password)
    {
        // Find user by email
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            // Log failed attempt
            $this->logFailedAttempt($email);
            return false;
        }

        // Check if account is locked due to too many failed attempts
        if ($this->isLocked($email)) {
            return false;
        }

        // Update last login
        $stmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $user['id']]);

        // Clear failed attempts
        $this->clearFailedAttempts($email);

        // Set session
        $this->login($user);

        return true;
    }

    /**
     * Login user (set session)
     */
    public function login($user)
    {
        $this->session->set('user_id', $user['id']);
        $this->session->set('user_email', $user['email']);
        $this->session->set('user_name', $user['name']);
        $this->session->set('user_role', $user['role']);
        $this->session->set('tenant_id', $user['tenant_id']);
        $this->session->set('authenticated', true);

        // Regenerate session ID for security
        $this->session->regenerate();
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $this->session->destroy();
    }

    /**
     * Check if user is authenticated
     */
    public function check()
    {
        return $this->session->get('authenticated') === true;
    }

    /**
     * Get current user
     */
    public function user()
    {
        if (!$this->check()) {
            return null;
        }

        return [
            'id' => $this->session->get('user_id'),
            'email' => $this->session->get('user_email'),
            'name' => $this->session->get('user_name'),
            'role' => $this->session->get('user_role'),
            'tenant_id' => $this->session->get('tenant_id')
        ];
    }

    /**
     * Check if user has role
     */
    public function hasRole($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $userRole = $this->session->get('user_role');
        return in_array($userRole, $roles);
    }

    /**
     * Check if user is platform admin
     */
    public function isPlatformAdmin()
    {
        return $this->hasRole('platform_admin');
    }

    /**
     * Check if user is tenant admin
     */
    public function isTenantAdmin()
    {
        return $this->hasRole('tenant_admin');
    }

    /**
     * Log failed login attempt
     */
    private function logFailedAttempt($email)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO login_attempts (email, ip_address, attempted_at)
            VALUES (:email, :ip, NOW())
        ");
        $stmt->execute([
            ':email' => $email,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }

    /**
     * Check if account is locked
     */
    private function isLocked($email)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) as attempts
            FROM login_attempts
            WHERE email = :email
            AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['attempts'] >= 5; // Lock after 5 failed attempts
    }

    /**
     * Clear failed attempts
     */
    private function clearFailedAttempts($email)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE email = :email");
        $stmt->execute([':email' => $email]);
    }
}
