<?php

namespace App\Livewire\Settings;

use App\Enums\CompanySettingKey;
use App\Enums\Currency;
use App\Services\CompanySettingService;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Title('Company settings')]
class Company extends Component
{
    use WithFileUploads;

    public mixed $logo = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $website = '';
    public string $default_rate = '';
    public string $currency = 'USD';
    public string $production_time = '';

    public function mount(CompanySettingService $settingService): void
    {
        $settings = $settingService->all();

        $this->name = $settings[CompanySettingKey::NAME->value] ?? '';
        $this->email = $settings[CompanySettingKey::EMAIL->value] ?? '';
        $this->phone = $settings[CompanySettingKey::PHONE->value] ?? '';
        $this->website = $settings[CompanySettingKey::WEBSITE->value] ?? '';
        $this->address = $settings[CompanySettingKey::ADDRESS->value] ?? '';
        $this->default_rate = $settings[CompanySettingKey::DEFAULT_RATE->value] ?? '';
        $this->currency = $settings[CompanySettingKey::CURRENCY->value] ?? 'USD';
        $this->production_time = $settings[CompanySettingKey::PRODUCTION_TIME->value] ?? '';
        $this->logo = $settings[CompanySettingKey::LOGO->value] ?? null;
    }

    public function save(CompanySettingService $settingService): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'default_rate' => 'required|numeric|min:0',
            'currency' => 'required|string|in:' . implode(',', array_column(Currency::cases(), 'value')),
            'production_time' => 'nullable|string|max:255',
            'logo' => 'nullable'
        ]);

        if ($this->logo instanceof TemporaryUploadedFile) {
            $validated['logo'] = $this->logo->store('company-logos', 'public');
        } else {
            $validated['logo'] = $this->logo;
        }

        $settingService->setMany($validated);

        $this->dispatch('company-settings-updated');
        
        Flux::toast(variant: 'success', text: __('Company settings updated.'));
    }
}
