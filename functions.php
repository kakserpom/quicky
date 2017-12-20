<?php
if (!function_exists('isInteger')) {
    function isInteger($var) {
        if (is_int($var)) {
            return true;
        }
        if (is_float($var)) {
            return true;
        }
        if (!is_string($var)) {
            return false;
        }
        return ctype_digit(substr($var, 0, 1) == '-' ? substr($var, 1) : $var);
    }
}
if (!function_exists('gpcvar_str')) {
    function gpcvar_str(&$var) {
        if (is_array($var)) {
            return '';
        }
        return (string)$var;
    }

    function gpcvar_strnull(&$var) {
        if ($var === null) {
            return null;
        }
        if (is_array($var)) {
            return '';
        }
        return (string)$var;
    }

    function gpcvar_int(&$var, $empty = false) {
        $var = (string)$var;
        if ($empty && !strlen($var)) {
            return $var;
        }
        return ctype_digit(substr($var, 0, 1) == '-' ? substr($var, 1) : $var) ? $var : '0';
    }

    function gpcvar_float(&$var, $empty = false) {
        if ($empty and strlen($var) == 0) {
            return '';
        }
        return floatval($var);
    }

    function gpcvar_array(&$var) {
        return is_array($var) ? $var : array();
    }

    function gpcvar_mixed(&$var) {
        return $var;
    }
}