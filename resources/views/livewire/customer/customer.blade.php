<section class="w-full">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Customers') }}</flux:heading>
            <flux:subheading>{{ __('Manage your customers here.') }}</flux:subheading>
        </div>

        <flux:modal.trigger name="customer-modal">
            <flux:button variant="primary" wire:click="create">{{ __('New Customer') }}</flux:button>
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
            <flux:table.column>{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($customers as $customer)
                <flux:table.row :key="$customer->id">
                    <flux:table.cell>{{ $customer->name }}</flux:table.cell>
                    <flux:table.cell>{{ $customer->phone }}</flux:cell>
                        <flux:table.cell>{{ $customer->email ?: '-' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button variant="subtle" size="sm" icon="pencil" wire:click="edit({{ $customer->id }})" />
                            <flux:button variant="subtle" size="sm" icon="trash" color="danger"
                                wire:click="delete({{ $customer->id }})"
                                wire:confirm="Are you sure you want to delete this customer?" />
                        </flux:table.cell>
                        </flux:row>
            @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center text-gray-500 py-8">
                                {{ __('No customers found.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>

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
</section>