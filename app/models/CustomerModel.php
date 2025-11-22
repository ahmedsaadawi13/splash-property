// FILE: /app/models/CustomerModel.php
<?php

/**
 * Customer Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class CustomerModel extends Model
{
    protected $table = 'customers';

    /**
     * Search customers
     */
    public function search($tenantId, $filters = [], $limit = 20, $offset = 0)
    {
        $sql = "SELECT c.*, u.name as assigned_to_name
                FROM {$this->table} c
                LEFT JOIN users u ON c.assigned_to_user_id = u.id
                WHERE c.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND c.type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['assigned_to'])) {
            $sql .= " AND c.assigned_to_user_id = :assigned_to";
            $params[':assigned_to'] = $filters['assigned_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (c.first_name LIKE :search OR c.last_name LIKE :search OR c.email LIKE :search OR c.phone LIKE :search OR c.customer_code LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";

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
     * Find by code
     */
    public function findByCode($code, $tenantId)
    {
        $stmt = $this->query("
            SELECT * FROM {$this->table}
            WHERE customer_code = :code AND tenant_id = :tenant_id
            LIMIT 1
        ", [
            ':code' => $code,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Generate next customer code
     */
    public function generateCode($tenantId)
    {
        $stmt = $this->query("
            SELECT customer_code FROM {$this->table}
            WHERE tenant_id = :tenant_id
            ORDER BY id DESC LIMIT 1
        ", [':tenant_id' => $tenantId]);

        $last = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($last) {
            $lastNumber = (int)filter_var($last['customer_code'], FILTER_SANITIZE_NUMBER_INT);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'CUST-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Count by type
     */
    public function countByType($tenantId)
    {
        $stmt = $this->query("
            SELECT type, COUNT(*) as count
            FROM {$this->table}
            WHERE tenant_id = :tenant_id
            GROUP BY type
        ", [':tenant_id' => $tenantId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counts = [];
        foreach ($results as $row) {
            $counts[$row['type']] = (int)$row['count'];
        }
        return $counts;
    }
}
