@props([
    'sidebar' => false,
])

@php
    $companyService = app(\App\Services\CompanySettingService::class);
    $companyName = $companyService->get(\App\Enums\CompanySettingKey::NAME) ?: config('app.name', 'Laravel');
    $companyLogo = $companyService->get(\App\Enums\CompanySettingKey::LOGO);
@endphp

@if($sidebar)
    <flux:sidebar.brand name="{{ $companyName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground overflow-hidden">
            @if($companyLogo)
                <img src="{{ Storage::url($companyLogo) }}" class="w-full h-full object-cover" alt="{{ $companyName }}" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="{{ $companyName }}" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground overflow-hidden">
            @if($companyLogo)
                <img src="{{ Storage::url($companyLogo) }}" class="w-full h-full object-cover" alt="{{ $companyName }}" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:brand>
@endif
