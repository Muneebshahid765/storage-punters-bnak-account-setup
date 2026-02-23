<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('sp_format_date')) {
    /**
     * Formats a date string to dd/mm/yyyy
     * 
     * @param string $date_string The date string to format
     * @param bool $include_time Whether to include time in the output
     * @return string Formatted date string
     */
    function sp_format_date($date_string, $include_time = false) {
        if (empty($date_string)) {
            return 'N/A';
        }

        $timestamp = strtotime($date_string);
        if (!$timestamp) {
            return $date_string;
        }

        $format = 'd/m/Y';
        if ($include_time) {
            $format .= ' H:i';
        }

        return date($format, $timestamp);
    }
}
