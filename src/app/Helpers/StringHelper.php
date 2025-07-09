<?php

if (!function_exists('normalizeSearchString')) {
    /**
     * @param string $string
     * @return string
     */
    function normalizeSearchString(string $string): string
    {
        return mb_strtolower(mb_convert_kana($string, 'asKV'));
    }
}
