// FILE: /app/models/TenantModel.php
<?php

/**
 * Tenant Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class TenantModel extends Model
{
    protected $table = 'tenants';
    protected $tenantScoped = false; // Tenants table is not scoped by tenant_id

    /**
     * Get tenant by code
     */
    public function findByCode($code)
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE code = :code LIMIT 1", [
            ':code' => $code
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get active tenants
     */
    public function getActive()
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update subscription usage counts
     */
    public function updateUsageCounts($tenantId)
    {
        // Get current counts
        $counts = [];

        // Units count
        $stmt = $this->query("SELECT COUNT(*) as count FROM units WHERE tenant_id = :tenant_id", [':tenant_id' => $tenantId]);
        $counts['units'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Customers count
        $stmt = $this->query("SELECT COUNT(*) as count FROM customers WHERE tenant_id = :tenant_id", [':tenant_id' => $tenantId]);
        $counts['customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Active contracts count
        $stmt = $this->query("SELECT COUNT(*) as count FROM contracts WHERE tenant_id = :tenant_id AND status = 'active'", [':tenant_id' => $tenantId]);
        $counts['contracts'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Users count
        $stmt = $this->query("SELECT COUNT(*) as count FROM users WHERE tenant_id = :tenant_id", [':tenant_id' => $tenantId]);
        $counts['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Update subscription
        $this->query("
            UPDATE tenant_subscriptions
            SET current_units_count = :units,
                current_customers_count = :customers,
                current_active_contracts_count = :contracts,
                current_users_count = :users
            WHERE tenant_id = :tenant_id
        ", [
            ':units' => $counts['units'],
            ':customers' => $counts['customers'],
            ':contracts' => $counts['contracts'],
            ':users' => $counts['users'],
            ':tenant_id' => $tenantId
        ]);

        return $counts;
    }
}
