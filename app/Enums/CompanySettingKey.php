<?php

namespace App\Enums;

enum CompanySettingKey: string
{
    case LOGO = 'logo';
    case NAME = 'name';
    case EMAIL = 'email';
    case PHONE = 'phone';
    case WEBSITE = 'website';
    case DEFAULT_RATE = 'default_rate';
    case CURRENCY = 'currency';
    case PRODUCTION_TIME = 'production_time';
    case ADDRESS = 'address';
}
