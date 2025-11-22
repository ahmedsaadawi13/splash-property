// FILE: /app/models/PlanModel.php
<?php

/**
 * Plan Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class PlanModel extends Model
{
    protected $table = 'plans';
    protected $tenantScoped = false;

    /**
     * Get active plans
     */
    public function getActive()
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY price");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get plan by code
     */
    public function findByCode($code)
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE code = :code LIMIT 1", [':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
