// FILE: /app/models/ContractModel.php
<?php

/**
 * Contract Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class ContractModel extends Model
{
    protected $table = 'contracts';

    /**
     * Get contracts with related data
     */
    public function getWithRelations($tenantId, $filters = [], $limit = 20, $offset = 0)
    {
        $sql = "SELECT c.*,
                       cu.full_name as customer_name,
                       u.unit_code,
                       u.unit_type,
                       us.name as created_by_name
                FROM {$this->table} c
                JOIN customers cu ON c.customer_id = cu.id
                JOIN units u ON c.unit_id = u.id
                LEFT JOIN users us ON c.created_by_user_id = us.id
                WHERE c.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['contract_type'])) {
            $sql .= " AND c.contract_type = :contract_type";
            $params[':contract_type'] = $filters['contract_type'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND c.customer_id = :customer_id";
            $params[':customer_id'] = $filters['customer_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (c.contract_code LIKE :search OR u.unit_code LIKE :search OR cu.full_name LIKE :search)";
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
            SELECT c.*,
                   cu.full_name as customer_name, cu.email as customer_email, cu.phone as customer_phone,
                   u.unit_code, u.unit_type, u.unit_number
            FROM {$this->table} c
            JOIN customers cu ON c.customer_id = cu.id
            JOIN units u ON c.unit_id = u.id
            WHERE c.contract_code = :code AND c.tenant_id = :tenant_id
            LIMIT 1
        ", [
            ':code' => $code,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Generate contract code
     */
    public function generateCode($tenantId, $type = 'sale')
    {
        $prefix = $type === 'sale' ? 'SAL-' : 'RNT-';

        $stmt = $this->query("
            SELECT contract_code FROM {$this->table}
            WHERE tenant_id = :tenant_id AND contract_type = :type
            ORDER BY id DESC LIMIT 1
        ", [
            ':tenant_id' => $tenantId,
            ':type' => $type
        ]);

        $last = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($last) {
            $lastNumber = (int)filter_var($last['contract_code'], FILTER_SANITIZE_NUMBER_INT);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get expiring contracts
     */
    public function getExpiring($tenantId, $days = 30)
    {
        $stmt = $this->query("
            SELECT c.*, cu.full_name as customer_name, u.unit_code
            FROM {$this->table} c
            JOIN customers cu ON c.customer_id = cu.id
            JOIN units u ON c.unit_id = u.id
            WHERE c.tenant_id = :tenant_id
            AND c.status = 'active'
            AND c.contract_type = 'rent'
            AND c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY c.end_date
        ", [
            ':tenant_id' => $tenantId,
            ':days' => $days
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
