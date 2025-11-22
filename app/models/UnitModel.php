// FILE: /app/models/UnitModel.php
<?php

/**
 * Unit Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class UnitModel extends Model
{
    protected $table = 'units';

    /**
     * Search units with filters
     */
    public function search($tenantId, $filters = [], $limit = 20, $offset = 0)
    {
        $sql = "SELECT u.*, p.name as project_name
                FROM {$this->table} u
                LEFT JOIN projects p ON u.project_id = p.id
                WHERE u.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['status'])) {
            $sql .= " AND u.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['unit_type'])) {
            $sql .= " AND u.unit_type = :unit_type";
            $params[':unit_type'] = $filters['unit_type'];
        }

        if (!empty($filters['listing_type'])) {
            $sql .= " AND u.listing_type IN (:listing_type, 'both')";
            $params[':listing_type'] = $filters['listing_type'];
        }

        if (!empty($filters['project_id'])) {
            $sql .= " AND u.project_id = :project_id";
            $params[':project_id'] = $filters['project_id'];
        }

        if (!empty($filters['price_min'])) {
            $sql .= " AND (u.sale_price >= :price_min OR u.rent_price >= :price_min)";
            $params[':price_min'] = $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $sql .= " AND (u.sale_price <= :price_max OR u.rent_price <= :price_max)";
            $params[':price_max'] = $filters['price_max'];
        }

        if (!empty($filters['bedrooms_min'])) {
            $sql .= " AND u.bedrooms >= :bedrooms_min";
            $params[':bedrooms_min'] = $filters['bedrooms_min'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (u.unit_code LIKE :search OR u.unit_number LIKE :search OR u.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get available units
     */
    public function getAvailable($tenantId, $listingType = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id AND status = 'available'";

        if ($listingType) {
            $sql .= " AND listing_type IN (:listing_type, 'both')";
        }

        $sql .= " ORDER BY unit_code";

        $params = [':tenant_id' => $tenantId];
        if ($listingType) {
            $params[':listing_type'] = $listingType;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find by code
     */
    public function findByCode($code, $tenantId)
    {
        $stmt = $this->query("
            SELECT u.*, p.name as project_name
            FROM {$this->table} u
            LEFT JOIN projects p ON u.project_id = p.id
            WHERE u.unit_code = :code AND u.tenant_id = :tenant_id
            LIMIT 1
        ", [
            ':code' => $code,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Count by status
     */
    public function countByStatus($tenantId)
    {
        $stmt = $this->query("
            SELECT status, COUNT(*) as count
            FROM {$this->table}
            WHERE tenant_id = :tenant_id
            GROUP BY status
        ", [':tenant_id' => $tenantId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counts = [];
        foreach ($results as $row) {
            $counts[$row['status']] = (int)$row['count'];
        }
        return $counts;
    }
}
