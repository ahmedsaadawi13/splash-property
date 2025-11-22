// FILE: /app/models/UserModel.php
<?php

/**
 * User Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class UserModel extends Model
{
    protected $table = 'users';

    /**
     * Find user by email
     */
    public function findByEmail($email, $tenantId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $sql .= " LIMIT 1";

        $params = [':email' => $email];
        if ($tenantId !== null) {
            $params[':tenant_id'] = $tenantId;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get users by tenant and role
     */
    public function getByRole($tenantId, $role)
    {
        $stmt = $this->query("
            SELECT * FROM {$this->table}
            WHERE tenant_id = :tenant_id AND role = :role AND status = 'active'
            ORDER BY name
        ", [
            ':tenant_id' => $tenantId,
            ':role' => $role
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get sales agents
     */
    public function getSalesAgents($tenantId)
    {
        $stmt = $this->query("
            SELECT * FROM {$this->table}
            WHERE tenant_id = :tenant_id
            AND role IN ('sales_agent', 'sales_manager')
            AND status = 'active'
            ORDER BY name
        ", [':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
