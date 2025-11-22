// FILE: /app/models/TenantSubscriptionModel.php
<?php

/**
 * Tenant Subscription Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class TenantSubscriptionModel extends Model
{
    protected $table = 'tenant_subscriptions';
    protected $tenantScoped = false;

    /**
     * Get active subscription for tenant
     */
    public function getActiveSubscription($tenantId)
    {
        $stmt = $this->query("
            SELECT ts.*, p.*
            FROM {$this->table} ts
            JOIN plans p ON ts.plan_id = p.id
            WHERE ts.tenant_id = :tenant_id AND ts.status IN ('active', 'trialing')
            ORDER BY ts.id DESC
            LIMIT 1
        ", [':tenant_id' => $tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if tenant can create more of a resource
     */
    public function canCreate($tenantId, $resourceType)
    {
        $subscription = $this->getActiveSubscription($tenantId);

        if (!$subscription) {
            return false;
        }

        switch ($resourceType) {
            case 'unit':
                return $subscription['current_units_count'] < $subscription['max_units'];
            case 'customer':
                return $subscription['current_customers_count'] < $subscription['max_customers'];
            case 'contract':
                return $subscription['current_active_contracts_count'] < $subscription['max_active_contracts'];
            case 'user':
                return $subscription['current_users_count'] < $subscription['max_users'];
            default:
                return false;
        }
    }

    /**
     * Check if subscription is active
     */
    public function isActive($tenantId)
    {
        $subscription = $this->getActiveSubscription($tenantId);
        return $subscription && in_array($subscription['status'], ['active', 'trialing']);
    }
}
