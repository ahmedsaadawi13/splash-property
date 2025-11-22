// FILE: /app/helpers/Validation.php
<?php

/**
 * Validation Helper
 * Handles input validation
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class Validation
{
    private $errors = [];
    private $data = [];

    /**
     * Validate data
     */
    public function validate($data, $rules)
    {
        $this->errors = [];
        $this->data = $data;

        foreach ($rules as $field => $ruleSet) {
            $ruleList = explode('|', $ruleSet);

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply validation rule
     */
    private function applyRule($field, $rule)
    {
        $value = $this->data[$field] ?? null;

        // Parse rule parameters
        $params = [];
        if (strpos($rule, ':') !== false) {
            list($rule, $paramString) = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, ucfirst($field) . ' is required');
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid email');
                }
                break;

            case 'min':
                if ($value && strlen($value) < (int)$params[0]) {
                    $this->addError($field, ucfirst($field) . ' must be at least ' . $params[0] . ' characters');
                }
                break;

            case 'max':
                if ($value && strlen($value) > (int)$params[0]) {
                    $this->addError($field, ucfirst($field) . ' must not exceed ' . $params[0] . ' characters');
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->addError($field, ucfirst($field) . ' must be numeric');
                }
                break;

            case 'integer':
                if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, ucfirst($field) . ' must be an integer');
                }
                break;

            case 'alpha':
                if ($value && !ctype_alpha(str_replace(' ', '', $value))) {
                    $this->addError($field, ucfirst($field) . ' must contain only letters');
                }
                break;

            case 'alphanumeric':
                if ($value && !ctype_alnum(str_replace(' ', '', $value))) {
                    $this->addError($field, ucfirst($field) . ' must contain only letters and numbers');
                }
                break;

            case 'in':
                if ($value && !in_array($value, $params)) {
                    $this->addError($field, ucfirst($field) . ' must be one of: ' . implode(', ', $params));
                }
                break;

            case 'date':
                if ($value && !strtotime($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid date');
                }
                break;

            case 'unique':
                // Format: unique:table,column
                if ($value && count($params) >= 2) {
                    $this->validateUnique($field, $value, $params[0], $params[1], $params[2] ?? null);
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value && (!isset($this->data[$confirmField]) || $value !== $this->data[$confirmField])) {
                    $this->addError($field, ucfirst($field) . ' confirmation does not match');
                }
                break;
        }
    }

    /**
     * Validate unique in database
     */
    private function validateUnique($field, $value, $table, $column, $exceptId = null)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = :value";

        if ($exceptId) {
            $sql .= " AND id != :except_id";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':value', $value);

        if ($exceptId) {
            $stmt->bindValue(':except_id', $exceptId);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            $this->addError($field, ucfirst($field) . ' already exists');
        }
    }

    /**
     * Add error
     */
    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Get errors
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get first error for field
     */
    public function first($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Check if has errors
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Sanitize string
     */
    public static function sanitize($value)
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean input
     */
    public static function clean($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'clean'], $data);
        }
        return self::sanitize($data);
    }
}
