// FILE: /app/models/VisitModel.php
<?php

/**
 * Visit Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class VisitModel extends Model
{
    protected $table = 'visits';

    /**
     * Get visits with relations
     */
    public function getWithRelations($tenantId, $filters = [])
    {
        $sql = "SELECT v.*,
                       c.full_name as customer_name,
                       u.unit_code, u.unit_type,
                       us.name as created_by_name
                FROM {$this->table} v
                JOIN customers c ON v.customer_id = c.id
                JOIN units u ON v.unit_id = u.id
                LEFT JOIN users us ON v.created_by_user_id = us.id
                WHERE v.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['status'])) {
            $sql .= " AND v.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND v.customer_id = :customer_id";
            $params[':customer_id'] = $filters['customer_id'];
        }

        if (!empty($filters['unit_id'])) {
            $sql .= " AND v.unit_id = :unit_id";
            $params[':unit_id'] = $filters['unit_id'];
        }

        $sql .= " ORDER BY v.scheduled_date DESC, v.scheduled_time DESC";

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming visits
     */
    public function getUpcoming($tenantId, $days = 7)
    {
        $stmt = $this->query("
            SELECT v.*, c.full_name as customer_name, u.unit_code
            FROM {$this->table} v
            JOIN customers c ON v.customer_id = c.id
            JOIN units u ON v.unit_id = u.id
            WHERE v.tenant_id = :tenant_id
            AND v.status = 'scheduled'
            AND v.scheduled_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY v.scheduled_date, v.scheduled_time
        ", [
            ':tenant_id' => $tenantId,
            ':days' => $days
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
