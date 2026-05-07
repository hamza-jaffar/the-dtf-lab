<section class="w-full">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Orders') }}</flux:heading>
            <flux:subheading>
                @if($phoneFilter)
                    {{ __('Showing orders for phone: ') }}<strong>{{ $phoneFilter }}</strong>
                    <flux:link href="{{ route('orders') }}" wire:navigate class="ml-2 text-sm">
                        {{ __('View all orders') }}
                    </flux:link>
                @else
                    {{ __('Manage all customer orders.') }}
                @endif
            </flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="$dispatch('open-order-form')">
            {{ __('New Order') }}
        </flux:button>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <flux:card class="flex flex-col gap-2 p-5 bg-gradient-to-br from-indigo-500/10 to-transparent border-indigo-500/20">
            <div class="flex items-center justify-between">
                <flux:icon name="banknotes" class="text-indigo-600 size-6" />
                <flux:badge color="indigo" size="sm">{{ __('Total Sales') }}</flux:badge>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    {{ format_money($stats['total_sales']) }}
                </div>
                <div class="text-xs text-zinc-500 mt-1">{{ __('Filtered revenue') }}</div>
            </div>
        </flux:card>

        <flux:card class="flex flex-col gap-2 p-5 bg-gradient-to-br from-emerald-500/10 to-transparent border-emerald-500/20">
            <div class="flex items-center justify-between">
                <flux:icon name="check-circle" class="text-emerald-600 size-6" />
                <flux:badge color="emerald" size="sm">{{ __('Collected') }}</flux:badge>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    {{ format_money($stats['total_collected']) }}
                </div>
                <div class="text-xs text-zinc-500 mt-1">{{ __('Actually received') }}</div>
            </div>
        </flux:card>

        <flux:card class="flex flex-col gap-2 p-5 bg-gradient-to-br from-amber-500/10 to-transparent border-amber-500/20">
            <div class="flex items-center justify-between">
                <flux:icon name="clock" class="text-amber-600 size-6" />
                <flux:badge color="amber" size="sm">{{ __('Pending') }}</flux:badge>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold tracking-tight text-red-500">
                    {{ format_money($stats['total_pending']) }}
                </div>
                <div class="text-xs text-zinc-500 mt-1">{{ __('Outstanding payments') }}</div>
            </div>
        </flux:card>
    </div>

    {{-- Search and Filters --}}
    <flux:card class="mb-6 p-4 bg-zinc-50/50 dark:bg-white/5 border-zinc-200/50 dark:border-white/10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-2 items-end">
            <div class="xl:col-span-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                    label="{{ __('Search') }}" placeholder="{{ __('Order # or name...') }}" type="search" />
            </div>

            <div>
                <flux:input type="date" wire:model.live="startDate" label="{{ __('From') }}" />
            </div>

            <div>
                <flux:input type="date" wire:model.live="endDate" label="{{ __('To') }}" />
            </div>

            <div>
                <flux:select wire:model.live="statusFilter" label="{{ __('Status') }}">
                    <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                    @foreach($statuses as $status)
                        <flux:select.option :value="$status->value">{{ $status->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex items-center">
                <flux:button icon="arrow-path" variant="subtle" class="w-full" wire:click="resetFilters">
                    {{ __('Reset') }}
                </flux:button>

                <flux:button icon="document-arrow-down" variant="subtle" class="w-full"
                    href="{{ route('orders.export.pdf', ['search' => $search, 'phone_number' => $phoneFilter, 'start_date' => $startDate, 'end_date' => $endDate, 'status' => $statusFilter]) }}"
                    target="_blank"
                    title="{{ __('Export Summary PDF') }}">
                    {{ __('Export') }}
                </flux:button>
            </div>
        </div>
    </flux:card>

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'order_number'" :direction="$sortDirection"
                wire:click="sort('order_number')">{{ __('Order #') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                wire:click="sort('created_at')">{{ __('Date') }}</flux:table.column>
            <flux:table.column>{{ __('Customer') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                wire:click="sort('status')">{{ __('Status') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'total_amount'" :direction="$sortDirection"
                wire:click="sort('total_amount')">{{ __('Total') }}</flux:table.column>
            <flux:table.column>{{ __('Paid') }}</flux:table.column>
            <flux:table.column>{{ __('Pending') }}</flux:table.column>
            <flux:table.column>{{ __('Items') }}</flux:table.column>
            <flux:table.column>{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($orders as $order)
                <flux:table.row :key="$order->id">
                    <flux:table.cell class="font-mono font-semibold">
                        <flux:link href="{{ route('orders.detail', ['id' => $order->id]) }}" wire:navigate>
                            {{ $order->order_number }}
                        </flux:link>
                    </flux:table.cell>
                    <flux:table.cell>{{ $order->created_at->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="font-medium">{{ $order->customer->name }}</div>
                        <div class="text-xs text-zinc-500">{{ $order->customer->phone }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($order->status->label() === 'Delivered')
                            <flux:badge :color="$order->status->color()" size="sm">
                                {{ $order->status->label() }}
                            </flux:badge>
                        @else
                            <flux:dropdown>
                                <flux:button class="p-0! h-fit cursor-pointer" variant="ghost" title="Click to change status">
                                    <flux:badge :color="$order->status->color()" size="sm">
                                        {{ $order->status->label() }}
                                    </flux:badge>
                                </flux:button>

                                <flux:menu class="w-fit!" style="width: fit-content !important;">
                                    <flux:menu.group heading="Change Status" class="w-fit!">
                                        @foreach ($statuses as $status)
                                            <flux:menu.item wire:click="updateStatus({{ $order->id }}, '{{ $status->value }}')"
                                                :icon="$order->status === $status ? 'check' : ''"
                                                class="{{ $order->status === $status ? 'font-semibold' : '' }}">
                                                <flux:badge :color="$status->color()" size="sm">
                                                    {{ $status->label() }}
                                                </flux:badge>
                                            </flux:menu.item>
                                        @endforeach
                                    </flux:menu.group>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ format_money($order->total_amount) }}</flux:table.cell>
                    <flux:table.cell>
                        <span class="{{ $order->paid_amount > 0 ? 'text-green-500 font-medium' : 'text-zinc-500' }}">
                            {{ format_money($order->paid_amount ?? 0) }}
                        </span>

                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="{{ $order->pending_amount > 0 ? 'text-red-500 font-medium' : 'text-zinc-500' }}">
                            {{ format_money($order->pending_amount) }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>{{ $order->items_count ?? $order->items->count() }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button variant="subtle" size="sm" icon="document-text" title="{{ __('Invoice') }}"
                                href="{{ route('orders.invoice.pdf', ['order' => $order->id]) }}" target="_blank" />
                            <flux:button variant="subtle" size="sm" icon="banknotes" title="{{ __('Record Payment') }}"
                                :disabled="$order->pending_amount <= 0"
                                wire:click="$dispatch('open-payment-modal', { orderId: {{ $order->id }} })" />
                            <flux:button variant="subtle" size="sm" icon="pencil"
                                wire:click="$dispatch('edit-order', { orderId: {{ $order->id }} })" />
                            <flux:button variant="subtle" size="sm" icon="trash" color="danger"
                                wire:click="deleteOrder({{ $order->id }})"
                                wire:confirm="Are you sure you want to delete order {{ $order->order_number }}?" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center text-zinc-500 py-12">
                        <flux:icon name="inbox" class="mx-auto mb-2 size-8 opacity-40" />
                        {{ __('No orders found.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>

    @livewire('order.order-form')
    @livewire('order.payment-form')
</section>
