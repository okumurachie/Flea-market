<?php

namespace App\Helpers;

class MethodHelper
{
    public static function getPaymentMethodLabel(?string $value): string
    {
        return match ($value) {
            'konbini' => 'コンビニ支払い',
            'card' => 'カード支払い',
            default => '選択してください',
        };
    }
}
