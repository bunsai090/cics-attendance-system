<?php
/**
 * Validation Utility Class
 * CICS Attendance System
 */

class Validator {
    public static function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);
            $value = $data[$field] ?? null;
            
            foreach ($rulesArray as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleValue = $ruleParts[1] ?? null;
                
                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = ucfirst($field) . ' is required';
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = ucfirst($field) . ' must be a valid email address';
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && strlen($value) < (int)$ruleValue) {
                            $errors[$field][] = ucfirst($field) . ' must be at least ' . $ruleValue . ' characters';
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && strlen($value) > (int)$ruleValue) {
                            $errors[$field][] = ucfirst($field) . ' must not exceed ' . $ruleValue . ' characters';
                        }
                        break;
                        
                    case 'match':
                        $matchField = $ruleValue;
                        if (!empty($value) && ($data[$matchField] ?? null) !== $value) {
                            $errors[$field][] = ucfirst($field) . ' does not match ' . $matchField;
                        }
                        break;
                        
                    case 'in':
                        $allowedValues = explode(',', $ruleValue);
                        if (!empty($value) && !in_array($value, $allowedValues)) {
                            $errors[$field][] = ucfirst($field) . ' must be one of: ' . $ruleValue;
                        }
                        break;
                        
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field][] = ucfirst($field) . ' must be numeric';
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

