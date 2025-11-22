// FILE: /app/models/PaymentScheduleModel.php
<?php

/**
 * Payment Schedule Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class PaymentScheduleModel extends Model
{
    protected $table = 'payment_schedules';

    /**
     * Get schedules for contract
     */
    public function getByContract($contractId, $tenantId)
    {
        $stmt = $this->query("
            SELECT * FROM {$this->table}
            WHERE contract_id = :contract_id AND tenant_id = :tenant_id
            ORDER BY installment_number
        ", [
            ':contract_id' => $contractId,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get overdue schedules
     */
    public function getOverdue($tenantId)
    {
        $stmt = $this->query("
            SELECT ps.*, c.contract_code, cu.full_name as customer_name, u.unit_code
            FROM {$this->table} ps
            JOIN contracts c ON ps.contract_id = c.id
            JOIN customers cu ON c.customer_id = cu.id
            JOIN units u ON c.unit_id = u.id
            WHERE ps.tenant_id = :tenant_id
            AND ps.status IN ('pending', 'partially_paid')
            AND ps.due_date < CURDATE()
            ORDER BY ps.due_date
        ", [':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming schedules
     */
    public function getUpcoming($tenantId, $days = 30)
    {
        $stmt = $this->query("
            SELECT ps.*, c.contract_code, cu.full_name as customer_name, u.unit_code
            FROM {$this->table} ps
            JOIN contracts c ON ps.contract_id = c.id
            JOIN customers cu ON c.customer_id = cu.id
            JOIN units u ON c.unit_id = u.id
            WHERE ps.tenant_id = :tenant_id
            AND ps.status = 'pending'
            AND ps.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY ps.due_date
        ", [
            ':tenant_id' => $tenantId,
            ':days' => $days
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update schedule status based on payments
     */
    public function updateStatus($scheduleId, $tenantId)
    {
        // Get total paid for this schedule
        $stmt = $this->query("
            SELECT COALESCE(SUM(amount_paid), 0) as total_paid
            FROM payments
            WHERE schedule_id = :schedule_id AND tenant_id = :tenant_id
        ", [
            ':schedule_id' => $scheduleId,
            ':tenant_id' => $tenantId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalPaid = $result['total_paid'];

        // Get schedule amount
        $schedule = $this->find($scheduleId, $tenantId);

        if (!$schedule) {
            return false;
        }

        $amountDue = $schedule['amount_due'];

        // Determine status
        if ($totalPaid >= $amountDue) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partially_paid';
        } elseif (strtotime($schedule['due_date']) < time()) {
            $status = 'overdue';
        } else {
            $status = 'pending';
        }

        return $this->update($scheduleId, ['status' => $status], $tenantId);
    }
}
