<section class="w-full">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Customers') }}</flux:heading>
            <flux:subheading>{{ __('Manage your customers here.') }}</flux:subheading>
        </div>

        <flux:modal.trigger name="customer-modal">
            <flux:button variant="primary" wire:click="create" icon="plus">{{ __('New Customer') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="mb-4 flex items-center justify-between">
        <div class="w-full max-w-sm">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search customers..." type="search" />
        </div>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'phone'" :direction="$sortDirection"
                wire:click="sort('phone')">{{ __('Phone') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'email'" :direction="$sortDirection"
                wire:click="sort('email')">{{ __('Email') }}</flux:table.column>
            <flux:table.column>{{ __('Orders') }}</flux:table.column>
            <flux:table.column>{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($customers as $customer)
                <flux:table.row :key="$customer->id">
                    <flux:table.cell class="font-medium">{{ $customer->name }}</flux:table.cell>
                    <flux:table.cell>{{ $customer->phone }}</flux:table.cell>
                    <flux:table.cell>{{ $customer->email ?: '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:link
                            href="{{ route('orders', ['phone_number' => $customer->phone]) }}"
                            wire:navigate
                            class="text-sm">
                            {{ $customer->orders_count ?? 0 }} {{ __('order(s)') }}
                        </flux:link>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            {{-- Add Order --}}
                            <flux:button
                                variant="subtle"
                                size="sm"
                                icon="plus-circle"
                                title="{{ __('Add Order') }}"
                                wire:click="$dispatch('open-order-form', { customerId: {{ $customer->id }} })" />

                            {{-- Edit Customer --}}
                            <flux:button
                                variant="subtle"
                                size="sm"
                                icon="pencil"
                                title="{{ __('Edit Customer') }}"
                                wire:click="edit({{ $customer->id }})" />

                            {{-- Delete Customer --}}
                            <flux:button
                                variant="subtle"
                                size="sm"
                                icon="trash"
                                color="danger"
                                title="{{ __('Delete Customer') }}"
                                wire:click="delete({{ $customer->id }})"
                                wire:confirm="Are you sure you want to delete {{ $customer->name }}?" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center text-zinc-500 py-12">
                        <flux:icon name="users" class="mx-auto mb-2 size-8 opacity-40" />
                        {{ __('No customers found.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>

    {{-- Customer CRUD Modal --}}
    <flux:modal name="customer-modal" class="md:w-[600px] space-y-6">
        <div>
            <flux:heading size="lg">{{ $isEditing ? __('Edit Customer') : __('New Customer') }}</flux:heading>
            <flux:subheading>{{ __('Fill in the details below to manage your customer information.') }}</flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="name" :label="__('Name')" placeholder="John Doe" type="text" required />
                <flux:input wire:model="phone" :label="__('Phone')" placeholder="+1 (555) 000-0000" type="text" required />
            </div>

            <flux:input wire:model="email" :label="__('Email')" placeholder="john@example.com" type="email" />

            <flux:textarea wire:model="address" :label="__('Address')" placeholder="123 Main St, City, Country" rows="3" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit">
                    {{ $isEditing ? __('Update Customer') : __('Create Customer') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Order Form Modal (listens for open-order-form event) --}}
    @livewire('order.order-form')
</section>