<section class="w-full">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Purchases') }}</flux:heading>
            <flux:subheading>{{ __('Manage your purchases and expenses here.') }}</flux:subheading>
        </div>

        <flux:modal.trigger name="purchase-modal">
            <flux:button variant="primary" wire:click="create" icon="plus">{{ __('New Purchase') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <flux:card
            class="flex flex-col gap-2 p-5 bg-gradient-to-br from-rose-500/10 to-transparent border-rose-500/20 col-span-1 md:col-span-1">
            <div class="flex items-center justify-between">
                <flux:icon name="shopping-bag" class="text-rose-600 size-6" />
                <flux:badge color="rose" size="sm">{{ __('Total Expenses') }}</flux:badge>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    {{ format_money($total_spent) }}
                </div>
                <div class="text-xs text-zinc-500 mt-1">{{ __('Total money spent on purchases') }}</div>
            </div>
        </flux:card>
    </div>

    <div class="mb-4 flex items-center justify-between">
        <div class="w-full max-w-sm">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Search purchases..." type="search" />
        </div>
        <div class="flex gap-2">
            <flux:button variant="subtle" icon="document-arrow-down" href="{{ route('purchases.export.pdf') }}?search={{ $search }}&sort={{ $sortBy }}&dir={{ $sortDirection }}" target="_blank">
                {{ __('Export PDF') }}
            </flux:button>
        </div>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'item_name'" :direction="$sortDirection"
                wire:click="sort('item_name')">{{ __('Item Name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'purchase_date'" :direction="$sortDirection"
                wire:click="sort('purchase_date')">{{ __('Date') }}</flux:table.column>
            <flux:table.column>{{ __('Quantity') }}</flux:table.column>
            <flux:table.column>{{ __('Unit Price') }}</flux:table.column>
            <flux:table.column>{{ __('Total Price') }}</flux:table.column>
            <flux:table.column>{{ __('Notes') }}</flux:table.column>
            <flux:table.column>{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($purchases as $purchase)
                <flux:table.row :key="$purchase->id">
                    <flux:table.cell class="font-medium">{{ $purchase->item_name }}</flux:table.cell>
                    <flux:table.cell>{{ $purchase->purchase_date->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>{{ $purchase->quantity }}</flux:table.cell>
                    <flux:table.cell>{{ format_money($purchase->unit_price) }}</flux:table.cell>
                    <flux:table.cell class="font-bold">{{ format_money($purchase->total_price) }}</flux:table.cell>
                    <flux:table.cell class="max-w-xs truncate">{{ $purchase->notes ?: '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            {{-- Edit Purchase --}}
                            <flux:button variant="subtle" size="sm" icon="pencil" title="{{ __('Edit Purchase') }}"
                                wire:click="edit({{ $purchase->id }})" />

                            {{-- Delete Purchase --}}
                            <flux:button variant="subtle" size="sm" icon="trash" color="danger"
                                title="{{ __('Delete Purchase') }}" wire:click="delete({{ $purchase->id }})"
                                wire:confirm="Are you sure you want to delete {{ $purchase->item_name }}?" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center text-zinc-500 py-12">
                        <flux:icon name="shopping-bag" class="mx-auto mb-2 size-8 opacity-40" />
                        {{ __('No purchases found.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $purchases->links() }}
    </div>

    {{-- Purchase CRUD Modal --}}
    <flux:modal name="purchase-modal" class="md:w-[600px] space-y-6" wire:ignore.self>
        <div>
            <flux:heading size="lg">{{ $isEditing ? __('Edit Purchase') : __('New Purchase') }}</flux:heading>
            <flux:subheading>{{ __('Fill in the details below to manage your purchase.') }}
            </flux:subheading>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:input wire:model="item_name" :label="__('Item Name')" placeholder="e.g. Printer Ink" type="text" required />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model.live="quantity" :label="__('Quantity')" type="number" min="1" required />
                <flux:input wire:model.live="unit_price" :label="__('Unit Price')" placeholder="0.00" type="number" step="any" min="0" required />
            </div>

            <div class="bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-lg flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Calculated Total Price') }}</span>
                <span class="text-lg font-bold text-zinc-900 dark:text-white" 
                      x-data="{ 
                          get total() { 
                              return (parseFloat($wire.quantity) || 0) * (parseFloat($wire.unit_price) || 0); 
                          } 
                      }" 
                      x-text="total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })">
                </span>
            </div>

            <flux:input wire:model="purchase_date" :label="__('Purchase Date')" type="date" required />

            <flux:textarea wire:model="notes" :label="__('Notes')" placeholder="Any details..." rows="3" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" type="submit">
                    {{ $isEditing ? __('Update Purchase') : __('Create Purchase') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
