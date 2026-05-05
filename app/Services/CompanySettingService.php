<?php

namespace App\Services;

use App\Models\CompanySettings;
use App\Enums\CompanySettingKey;

class CompanySettingService
{
    /**
     * Get a specific setting value.
     */
    public function get(CompanySettingKey $key, mixed $default = null): mixed
    {
        $setting = CompanySettings::where('key', $key->value)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get all settings as a key-value pair.
     */
    public function all(): array
    {
        return CompanySettings::pluck('value', 'key')->toArray();
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
    }

    /**
     * Set multiple settings at once.
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $enumKey = CompanySettingKey::tryFrom($key);
            if ($enumKey) {
                $this->set($enumKey, $value);
            }
        }
    }
}
