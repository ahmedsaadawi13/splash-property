// FILE: /app/models/PaymentModel.php
<?php

/**
 * Payment Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class PaymentModel extends Model
{
    protected $table = 'payments';

    /**
     * Get payments for contract
     */
    public function getByContract($contractId, $tenantId)
    {
        $stmt = $this->query("
            SELECT p.*, u.name as received_by_name
            FROM {$this->table} p
            LEFT JOIN users u ON p.received_by_user_id = u.id
            WHERE p.contract_id = :contract_id AND p.tenant_id = :tenant_id
            ORDER BY p.payment_date DESC
        ", [
            ':contract_id' => $contractId,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total paid for contract
     */
    public function getTotalPaidByContract($contractId, $tenantId)
    {
        $stmt = $this->query("
            SELECT COALESCE(SUM(amount_paid), 0) as total
            FROM {$this->table}
            WHERE contract_id = :contract_id AND tenant_id = :tenant_id
        ", [
            ':contract_id' => $contractId,
            ':tenant_id' => $tenantId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)$result['total'];
    }

    /**
     * Get payments by date range
     */
    public function getByDateRange($tenantId, $startDate, $endDate)
    {
        $stmt = $this->query("
            SELECT p.*, c.contract_code, cu.full_name as customer_name, u.unit_code
            FROM {$this->table} p
            JOIN contracts c ON p.contract_id = c.id
            JOIN customers cu ON c.customer_id = cu.id
            JOIN units u ON c.unit_id = u.id
            WHERE p.tenant_id = :tenant_id
            AND p.payment_date BETWEEN :start_date AND :end_date
            ORDER BY p.payment_date DESC
        ", [
            ':tenant_id' => $tenantId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total collected by period
     */
    public function getTotalByPeriod($tenantId, $startDate, $endDate)
    {
        $stmt = $this->query("
            SELECT COALESCE(SUM(amount_paid), 0) as total
            FROM {$this->table}
            WHERE tenant_id = :tenant_id
            AND payment_date BETWEEN :start_date AND :end_date
        ", [
            ':tenant_id' => $tenantId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)$result['total'];
    }
}
