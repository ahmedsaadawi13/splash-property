// FILE: /app/models/ActivityLogModel.php
<?php

/**
 * Activity Log Model
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class ActivityLogModel extends Model
{
    protected $table = 'activity_logs';

    /**
     * Log activity
     */
    public function log($tenantId, $userId, $entityType, $entityId, $action, $description = '')
    {
        return $this->create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }

    /**
     * Get recent activities
     */
    public function getRecent($tenantId, $limit = 50)
    {
        $stmt = $this->query("
            SELECT a.*, u.name as user_name
            FROM {$this->table} a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.tenant_id = :tenant_id
            ORDER BY a.created_at DESC
            LIMIT :limit
        ", [':tenant_id' => $tenantId]);

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get activities for entity
     */
    public function getByEntity($entityType, $entityId, $tenantId)
    {
        $stmt = $this->query("
            SELECT a.*, u.name as user_name
            FROM {$this->table} a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.entity_type = :entity_type
            AND a.entity_id = :entity_id
            AND a.tenant_id = :tenant_id
            ORDER BY a.created_at DESC
        ", [
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':tenant_id' => $tenantId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
