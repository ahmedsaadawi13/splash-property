// FILE: /app/models/ProjectModel.php
<?php

/**
 * Project Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class ProjectModel extends Model
{
    protected $table = 'projects';

    /**
     * Get project with units count
     */
    public function getWithStats($tenantId)
    {
        $stmt = $this->query("
            SELECT p.*,
                   COUNT(u.id) as units_count,
                   SUM(CASE WHEN u.status = 'available' THEN 1 ELSE 0 END) as available_units,
                   SUM(CASE WHEN u.status = 'sold' THEN 1 ELSE 0 END) as sold_units,
                   SUM(CASE WHEN u.status = 'rented' THEN 1 ELSE 0 END) as rented_units
            FROM {$this->table} p
            LEFT JOIN units u ON p.id = u.project_id AND u.tenant_id = p.tenant_id
            WHERE p.tenant_id = :tenant_id
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ", [':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find project by code
     */
    public function findByCode($code, $tenantId)
    {
        $stmt = $this->query("
            SELECT * FROM {$this->table}
            WHERE code = :code AND tenant_id = :tenant_id
            LIMIT 1
        ", [
            ':code' => $code,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
