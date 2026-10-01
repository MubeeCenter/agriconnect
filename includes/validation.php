<?php
/**
 * AgriConnect — Server-side Validation
 * Mirrors validation.js rules. Always validate on the server.
 */

require_once __DIR__ . '/config.php';

// ============================================
// PRIMITIVE VALIDATORS
// ============================================

function v_required($value) {
    return $value !== null && trim((string)$value) !== '';
}

function v_email($value) {
    return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
}

function v_phone_ng($value) {
    $v = preg_replace('/\s+/', '', (string)$value);
    // Accepts 080..., 070..., 081..., 090..., 091..., or +234...
    return (bool)preg_match('/^(\+?234|0)[789][01]\d{8}$/', $v);
}

function v_integer($value) {
    return preg_match('/^-?\d+$/', (string)$value) === 1;
}

function v_number($value) {
    return is_numeric($value);
}

function v_min_length($value, $min) {
    return mb_strlen(trim((string)$value)) >= (int)$min;
}

function v_max_length($value, $max) {
    return mb_strlen(trim((string)$value)) <= (int)$max;
}

function v_min_value($value, $min) {
    return is_numeric($value) && (float)$value >= (float)$min;
}

function v_max_value($value, $max) {
    return is_numeric($value) && (float)$value <= (float)$max;
}

function v_in_array($value, array $allowed) {
    return in_array($value, $allowed, true);
}

function v_date($value) {
    return strtotime((string)$value) !== false;
}

// ============================================
// SANITIZATION
// ============================================

function sanitize_string($value, $maxLen = null) {
    $value = trim((string)$value);
    $value = strip_tags($value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    if ($maxLen !== null) {
        $value = mb_substr($value, 0, (int)$maxLen);
    }
    return $value;
}

function sanitize_phone($value) {
    $v = preg_replace('/\s+/', '', (string)$value);
    if (strpos($v, '+234') === 0) {
        $v = '0' . substr($v, 4);
    } elseif (strpos($v, '234') === 0 && strlen($v) > 3) {
        $v = '0' . substr($v, 3);
    }
    return substr($v, 0, 11);
}

function sanitize_int($value, $default = 0) {
    return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT) ?: $default;
}

function sanitize_float($value, $default = 0.0) {
    $v = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    return is_numeric($v) ? (float)$v : $default;
}

function sanitize_slug($value) {
    $value = strtolower(trim((string)$value));
    $value = preg_replace('/[^a-z0-9-]+/', '-', $value);
    $value = preg_replace('/-+/', '-', $value);
    return trim($value, '-');
}

// ============================================
// FORM VALIDATION ENGINE
// ============================================

/**
 * Validate data against a rule set.
 *
 * Rules format:
 *   [
 *     'field_name' => [
 *         'label'    => 'Business Name',
 *         'rules'    => 'required|min:3|max:150',
 *         'sanitize' => 'string',
 *     ],
 *     ...
 *   ]
 *
 * @return array ['valid' => bool, 'errors' => [], 'data' => []]
 */
function validate_form(array $data, array $rules) {
    $errors   = [];
    $clean    = [];

    foreach ($rules as $field => $config) {
        $label    = $config['label']    ?? ucfirst($field);
        $rulesStr = $config['rules']    ?? '';
        $sanitize = $config['sanitize'] ?? 'string';

        $raw = $data[$field] ?? null;

        // Sanitize
        switch ($sanitize) {
            case 'phone':   $value = sanitize_phone($raw);           break;
            case 'int':     $value = sanitize_int($raw);             break;
            case 'float':   $value = sanitize_float($raw);           break;
            case 'email':   $value = trim((string)$raw);             break;
            case 'slug':    $value = sanitize_slug($raw);            break;
            case 'raw':     $value = $raw;                           break;
            case 'string':
            default:        $value = sanitize_string($raw, 2000);    break;
        }

        $clean[$field] = $value;

        if ($rulesStr === '') continue;

        $ruleList = explode('|', $rulesStr);

        foreach ($ruleList as $rule) {
            $rule = trim($rule);
            if ($rule === '') continue;

            $param = null;
            if (strpos($rule, ':') !== false) {
                [$rule, $param] = explode(':', $rule, 2);
            }

            $passed = true;
            $msg    = '';

            switch ($rule) {
                case 'required':
                    if (!v_required($value)) {
                        $passed = false;
                        $msg    = "$label is required.";
                    }
                    break;

                case 'email':
                    if (v_required($value) && !v_email($value)) {
                        $passed = false;
                        $msg    = "$label must be a valid email address.";
                    }
                    break;

                case 'phone':
                    if (v_required($value) && !v_phone_ng($value)) {
                        $passed = false;
                        $msg    = "$label must be a valid Nigerian phone number.";
                    }
                    break;

                case 'integer':
                    if (v_required($value) && !v_integer($value)) {
                        $passed = false;
                        $msg    = "$label must be a whole number.";
                    }
                    break;

                case 'number':
                    if (v_required($value) && !v_number($value)) {
                        $passed = false;
                        $msg    = "$label must be a number.";
                    }
                    break;

                case 'min':
                    if (v_required($value) && !v_min_length($value, $param)) {
                        $passed = false;
                        $msg    = "$label must be at least $param characters.";
                    }
                    break;

                case 'max':
                    if (v_required($value) && !v_max_length($value, $param)) {
                        $passed = false;
                        $msg    = "$label must be at most $param characters.";
                    }
                    break;

                case 'min_value':
                    if (v_required($value) && !v_min_value($value, $param)) {
                        $passed = false;
                        $msg    = "$label must be at least $param.";
                    }
                    break;

                case 'max_value':
                    if (v_required($value) && !v_max_value($value, $param)) {
                        $passed = false;
                        $msg    = "$label must be at most $param.";
                    }
                    break;

                case 'in':
                    $allowed = explode(',', $param);
                    if (v_required($value) && !v_in_array($value, $allowed)) {
                        $passed = false;
                        $msg    = "$label must be one of: " . implode(', ', $allowed) . '.';
                    }
                    break;

                case 'date':
                    if (v_required($value) && !v_date($value)) {
                        $passed = false;
                        $msg    = "$label must be a valid date.";
                    }
                    break;
            }

            if (!$passed) {
                $errors[$field] = $msg;
                break; // Stop on first failure per field
            }
        }
    }

    return [
        'valid'  => empty($errors),
        'errors' => $errors,
        'data'   => $clean
    ];
}

// ============================================
// FLASH BACK OLD INPUT (for form redisplay)
// ============================================

function old_input_set(array $data) {
    unset($data['_csrf']);
    $_SESSION['_old_input'] = $data;
}

function old_input_get($field, $default = '') {
    return $_SESSION['_old_input'][$field] ?? $default;
}

function old_input_clear() {
    unset($_SESSION['_old_input']);
}

function old_input_has() {
    return !empty($_SESSION['_old_input']);
}

function error_get($field, $default = '') {
    return $_SESSION['_errors'][$field] ?? $default;
}

function errors_set(array $errors) {
    $_SESSION['_errors'] = $errors;
}

function errors_clear() {
    unset($_SESSION['_errors']);
}

function errors_has() {
    return !empty($_SESSION['_errors']);
}
?>