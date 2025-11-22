// FILE: /app/core/Model.php
<?php

/**
 * Base Model Class
 * All models extend this class
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $tenantScoped = true; // Most models are tenant-scoped

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find record by ID
     */
    public function find($id, $tenantId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";

        if ($this->tenantScoped && $tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);

        if ($this->tenantScoped && $tenantId !== null) {
            $stmt->bindValue(':tenant_id', $tenantId);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all records
     */
    public function all($tenantId = null, $orderBy = null)
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($this->tenantScoped && $tenantId !== null) {
            $sql .= " WHERE tenant_id = :tenant_id";
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        $stmt = $this->db->prepare($sql);

        if ($this->tenantScoped && $tenantId !== null) {
            $stmt->bindValue(':tenant_id', $tenantId);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new record
     */
    public function create($data)
    {
        $fields = array_keys($data);
        $values = array_values($data);

        $fieldList = implode(', ', $fields);
        $placeholders = implode(', ', array_map(function($f) { return ":$f"; }, $fields));

        $sql = "INSERT INTO {$this->table} ({$fieldList}) VALUES ({$placeholders})";

        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update record
     */
    public function update($id, $data, $tenantId = null)
    {
        $fields = array_keys($data);
        $setClause = implode(', ', array_map(function($f) { return "$f = :$f"; }, $fields));

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";

        if ($this->tenantScoped && $tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);

        if ($this->tenantScoped && $tenantId !== null) {
            $stmt->bindValue(':tenant_id', $tenantId);
        }

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }

    /**
     * Delete record
     */
    public function delete($id, $tenantId = null)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";

        if ($this->tenantScoped && $tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);

        if ($this->tenantScoped && $tenantId !== null) {
            $stmt->bindValue(':tenant_id', $tenantId);
        }

        return $stmt->execute();
    }

    /**
     * Execute custom query
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Count records
     */
    public function count($tenantId = null, $where = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $conditions = [];
        $params = [];

        if ($this->tenantScoped && $tenantId !== null) {
            $conditions[] = "tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        foreach ($where as $field => $value) {
            $conditions[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
}
