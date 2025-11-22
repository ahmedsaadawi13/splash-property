// FILE: /app/helpers/DateHelper.php
<?php

/**
 * DateHelper
 * Date formatting and manipulation utilities
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class DateHelper
{
    /**
     * Format date
     */
    public static function format($date, $format = 'Y-m-d')
    {
        if (!$date) {
            return '';
        }

        if (is_string($date)) {
            $date = strtotime($date);
        }

        return date($format, $date);
    }

    /**
     * Format datetime
     */
    public static function formatDateTime($datetime, $format = 'Y-m-d H:i:s')
    {
        return self::format($datetime, $format);
    }

    /**
     * Human readable date (e.g., "2 days ago")
     */
    public static function ago($date)
    {
        if (!$date) {
            return '';
        }

        $timestamp = is_string($date) ? strtotime($date) : $date;
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        } else {
            $years = floor($diff / 31536000);
            return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
        }
    }

    /**
     * Get current UTC datetime
     */
    public static function nowUtc()
    {
        return gmdate('Y-m-d H:i:s');
    }

    /**
     * Get current date
     */
    public static function today()
    {
        return date('Y-m-d');
    }

    /**
     * Check if date is in the past
     */
    public static function isPast($date)
    {
        $timestamp = is_string($date) ? strtotime($date) : $date;
        return $timestamp < time();
    }

    /**
     * Check if date is in the future
     */
    public static function isFuture($date)
    {
        $timestamp = is_string($date) ? strtotime($date) : $date;
        return $timestamp > time();
    }

    /**
     * Add days to date
     */
    public static function addDays($date, $days)
    {
        $timestamp = is_string($date) ? strtotime($date) : $date;
        return date('Y-m-d', strtotime("+{$days} days", $timestamp));
    }

    /**
     * Subtract days from date
     */
    public static function subDays($date, $days)
    {
        $timestamp = is_string($date) ? strtotime($date) : $date;
        return date('Y-m-d', strtotime("-{$days} days", $timestamp));
    }

    /**
     * Get difference in days
     */
    public static function diffInDays($date1, $date2)
    {
        $timestamp1 = is_string($date1) ? strtotime($date1) : $date1;
        $timestamp2 = is_string($date2) ? strtotime($date2) : $date2;

        return floor(($timestamp2 - $timestamp1) / 86400);
    }

    /**
     * Check if date is today
     */
    public static function isToday($date)
    {
        return self::format($date, 'Y-m-d') === self::today();
    }

    /**
     * Get start of month
     */
    public static function startOfMonth($date = null)
    {
        $date = $date ?: time();
        $timestamp = is_string($date) ? strtotime($date) : $date;
        return date('Y-m-01', $timestamp);
    }

    /**
     * Get end of month
     */
    public static function endOfMonth($date = null)
    {
        $date = $date ?: time();
        $timestamp = is_string($date) ? strtotime($date) : $date;
        return date('Y-m-t', $timestamp);
    }
}
