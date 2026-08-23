<?php

namespace DevDay\Helpers;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && ($value === null || trim((string)$value) === '')) {
                    $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                    break;
                }

                if ($value !== null && trim((string)$value) !== '') {
                    if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$field] = 'Please provide a valid email address.';
                        break;
                    }

                    if (str_starts_with($rule, 'min:')) {
                        $min = (int)substr($rule, 4);
                        if (mb_strlen((string)$value) < $min) {
                            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.";
                            break;
                        }
                    }

                    if (str_starts_with($rule, 'max:')) {
                        $max = (int)substr($rule, 4);
                        if (mb_strlen((string)$value) > $max) {
                            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " cannot exceed {$max} characters.";
                            break;
                        }
                    }

                    if (str_starts_with($rule, 'in:')) {
                        $allowed = explode(',', substr($rule, 3));
                        if (!in_array($value, $allowed, true)) {
                            $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' contains an invalid option.';
                            break;
                        }
                    }

                    if ($rule === 'integer' && !filter_var($value, FILTER_VALIDATE_INT) && $value !== '0' && $value !== 0) {
                        $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be an integer.';
                        break;
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}
