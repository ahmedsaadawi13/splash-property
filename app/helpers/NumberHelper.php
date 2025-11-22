// FILE: /app/helpers/NumberHelper.php
<?php

/**
 * NumberHelper
 * Number formatting utilities
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class NumberHelper
{
    /**
     * Format currency
     */
    public static function formatCurrency($amount, $currency = 'SAR', $decimals = 2)
    {
        $formatted = number_format($amount, $decimals, '.', ',');

        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'SAR' => 'SAR',
            'AED' => 'AED',
            'EGP' => 'EGP'
        ];

        $symbol = $symbols[$currency] ?? $currency;

        // For currencies with symbol before amount
        if (in_array($currency, ['USD', 'EUR', 'GBP'])) {
            return $symbol . $formatted;
        }

        // For currencies with symbol after amount
        return $formatted . ' ' . $symbol;
    }

    /**
     * Format number
     */
    public static function format($number, $decimals = 0)
    {
        return number_format($number, $decimals, '.', ',');
    }

    /**
     * Format percentage
     */
    public static function formatPercentage($number, $decimals = 2)
    {
        return number_format($number, $decimals, '.', ',') . '%';
    }

    /**
     * Format file size
     */
    public static function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /**
     * Format area
     */
    public static function formatArea($area, $unit = 'sqm')
    {
        return number_format($area, 2) . ' ' . $unit;
    }

    /**
     * Parse number from string
     */
    public static function parse($string)
    {
        // Remove common formatting characters
        $cleaned = str_replace([',', ' '], '', $string);
        return is_numeric($cleaned) ? (float)$cleaned : 0;
    }

    /**
     * Calculate percentage
     */
    public static function percentage($value, $total)
    {
        if ($total == 0) {
            return 0;
        }
        return ($value / $total) * 100;
    }

    /**
     * Round to nearest
     */
    public static function roundTo($number, $nearest = 5)
    {
        return round($number / $nearest) * $nearest;
    }
}
