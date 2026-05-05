<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Company settings') }}</flux:heading>

    <x-settings.layout :heading="__('Company')" :subheading="__('Update the company detail here')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <div class="border border-gray-300 dark:border-gray-600 w-32 rounded-full mx-auto h-32 overflow-hidden flex items-center justify-center bg-gray-50 dark:bg-gray-800">
                @if ($logo && is_string($logo))
                    <img src="{{ Storage::url($logo) }}" class="w-full h-full object-cover" />
                @elseif ($logo && !is_string($logo))
                    <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover" />
                @else
                    <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                @endif
            </div>
            
            <flux:input wire:model="logo" :label="__('Logo')" type="file" autofocus autocomplete="logo" />
            
            <flux:input wire:model="name" :label="__('Name')" placeholder="Enter the company name" type="text" required
                autofocus autocomplete="name" />
                
            <flux:input wire:model="email" :label="__('Email')" placeholder="Enter the company email" type="email"
                required autocomplete="email" />
                
            <flux:input wire:model="phone" :label="__('Phone')" placeholder="Enter the company phone" type="telephone"
                required autocomplete="phone" />

            <flux:input wire:model="website" :label="__('Website')" placeholder="https://example.com" type="url"
                autocomplete="url" />
                
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                <flux:input wire:model="default_rate" :label="__('Rate per inch')"
                    placeholder="Enter the default rate per inch" type="number" required autocomplete="default_rate" step="0.01" />

                <flux:select wire:model="currency" :label="__('Currency')">
                    @foreach (\App\Enums\Currency::cases() as $currencyOption)
                        <option value="{{ $currencyOption->value }}">{{ $currencyOption->label() }}</option>
                    @endforeach
                </flux:select>
            </div>

            <flux:input wire:model="production_time" :label="__('Standard Production Time')"
                placeholder="{{ __('e.g. 2-3 Business Days') }}" />

            <flux:textarea wire:model="address" :label="__('Company Address')" :rows="2"
                placeholder="Enter the company address"></flux:textarea>
                
            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>
