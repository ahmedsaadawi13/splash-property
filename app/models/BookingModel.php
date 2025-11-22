// FILE: /app/models/BookingModel.php
<?php

/**
 * Booking Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class BookingModel extends Model
{
    protected $table = 'bookings';

    /**
     * Get bookings with relations
     */
    public function getWithRelations($tenantId, $status = null)
    {
        $sql = "SELECT b.*,
                       c.full_name as customer_name,
                       u.unit_code, u.unit_type
                FROM {$this->table} b
                JOIN customers c ON b.customer_id = c.id
                JOIN units u ON b.unit_id = u.id
                WHERE b.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if ($status) {
            $sql .= " AND b.booking_status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate booking code
     */
    public function generateCode($tenantId)
    {
        $stmt = $this->query("
            SELECT booking_code FROM {$this->table}
            WHERE tenant_id = :tenant_id
            ORDER BY id DESC LIMIT 1
        ", [':tenant_id' => $tenantId]);

        $last = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($last) {
            $lastNumber = (int)filter_var($last['booking_code'], FILTER_SANITIZE_NUMBER_INT);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'BKG-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get expired bookings
     */
    public function getExpired($tenantId)
    {
        $stmt = $this->query("
            SELECT * FROM {$this->table}
            WHERE tenant_id = :tenant_id
            AND booking_status = 'active'
            AND reserved_until_date < CURDATE()
        ", [':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
