<?php

if (!function_exists('formatCurrency')) {
    /**
     * Format amount as UGX currency with thousand separators
     * 
     * @param float|int $amount
     * @return string
     */
    function formatCurrency($amount)
    {
        return number_format($amount, 0, '.', ',') . ' UGX';
    }
} 