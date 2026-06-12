<?php

if (!function_exists('format_harga')) {
    function format_harga($value)
    {
        return number_format((float) $value, 2, ',', '.');
    }
}
