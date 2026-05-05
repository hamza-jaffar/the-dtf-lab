<?php

namespace App\Enums;

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case PKR = 'PKR';

    public function symbol(): string
    {
        return match($this) {
            self::USD => '$',
            self::EUR => '€',
            self::GBP => '£',
            self::PKR => 'Rs',
        };
    }
    
    public function label(): string
    {
        return "{$this->value} ({$this->symbol()})";
    }
}
