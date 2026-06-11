<?php

if (!function_exists('format_harga')) {
    /**
     * Format angka harga dengan titik (.) sebagai desimal, tanpa trailing zero.
     * Contoh: 12.5 -> "12.5", 1000 -> "1,000", 0.00012 -> "0.00012"
     */
    function format_harga($value)
    {
        $f = (float) $value;
        // Maksimal 6 desimal, hapus trailing zero
        $formatted = rtrim(rtrim(number_format($f, 6, '.', ','), '0'), '.');
        return $formatted;
    }
}
