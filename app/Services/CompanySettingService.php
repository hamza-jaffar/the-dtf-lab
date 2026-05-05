<?php

namespace App\Services;

use App\Models\CompanySettings;
use App\Enums\CompanySettingKey;
use Illuminate\Support\Facades\Cache;

class CompanySettingService
{
    private const CACHE_KEY = 'company_settings';

    /**
     * Get a specific setting value.
     */
    public function get(CompanySettingKey $key, mixed $default = null): mixed
    {
        $settings = $this->all();
        
        return $settings[$key->value] ?? $default;
    }

    /**
     * Get all settings as a key-value pair.
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return CompanySettings::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Set a specific setting value.
     */
    public function set(CompanySettingKey $key, mixed $value): void
    {
        CompanySettings::updateOrCreate(
            ['key' => $key->value],
            ['value' => $value]
        );
        
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Set multiple settings at once.
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $enumKey = CompanySettingKey::tryFrom($key);
            if ($enumKey) {
                CompanySettings::updateOrCreate(
                    ['key' => $enumKey->value],
                    ['value' => $value]
                );
            }
        }
        
        Cache::forget(self::CACHE_KEY);
    }
}
