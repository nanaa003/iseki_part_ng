<?php

if (!function_exists('format_harga')) {
    function format_harga($value)
    {
        return rtrim(rtrim(number_format((float) $value, 10, ',', '.'), '0'), ',');
    }
}
