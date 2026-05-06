<?php

use App\Enums\CompanySettingKey;
use App\Enums\Currency;
use App\Services\CompanySettingService;

if (!function_exists('format_money')) {
    function format_money(?float $amount): string
    {
        $settings = app(CompanySettingService::class);
        $currency = $settings->get(CompanySettingKey::CURRENCY);
        $currencyEnum = Currency::tryFrom($currency);
        $symbol = $currencyEnum ? $currencyEnum->symbol() : '$';
        $formated_amount = number_format($amount);
        return "{$symbol} {$formated_amount}";

    }
}
