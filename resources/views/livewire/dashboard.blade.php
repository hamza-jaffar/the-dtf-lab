<div class="space-y-8">
    {{-- Welcome Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="mb-1">{{ __('Dashboard Overview') }}</flux:heading>
            <flux:subheading>{{ __('Welcome back! Here is what is happening with your lab today.') }}</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button icon="plus" variant="primary" wire:click="$dispatch('open-order-form')">{{ __('New Order') }}</flux:button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <flux:card class="flex flex-col gap-2 p-5 bg-gradient-to-br from-indigo-500/10 to-transparent border-indigo-500/20">
            <div class="flex items-center justify-between">
                <flux:icon name="banknotes" class="text-indigo-600 size-6" />
                <flux:badge color="indigo" size="sm">{{ __('Total Sales') }}</flux:badge>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    {{ format_money($stats['total_sales']) }}
                </div>
                <div class="text-xs text-zinc-500 mt-1">{{ __('Lifetime revenue generated') }}</div>
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
                <div class="text-xs text-zinc-500 mt-1">{{ __('Cash actually in hand') }}</div>
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

        <flux:card class="flex flex-col gap-2 p-5 bg-gradient-to-br from-purple-500/10 to-transparent border-purple-500/20">
            <div class="flex items-center justify-between">
                <flux:icon name="users" class="text-purple-600 size-6" />
                <flux:badge color="purple" size="sm">{{ __('Customers') }}</flux:badge>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    {{ number_format($stats['total_customers']) }}
                </div>
                <div class="text-xs text-zinc-500 mt-1">{{ __('Registered clients') }}</div>
            </div>
        </flux:card>
    </div>

    {{-- Secondary Row: Status & Recent --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Status Distribution --}}
        <flux:card class="lg:col-span-1 space-y-4">
            <flux:heading size="md">{{ __('Order Status') }}</flux:heading>
            <div class="space-y-3">
                @foreach($statuses as $status)
                    @php $count = $statusDistribution[$status->value] ?? 0; @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full" style="background-color: var(--flux-color-{{ $status->color() }}-500)"></div>
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $status->label() }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ $count }}</span>
                            <div class="w-24 h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" 
                                     style="width: {{ $stats['total_orders'] > 0 ? ($count / $stats['total_orders'] * 100) : 0 }}%; background-color: var(--flux-color-{{ $status->color() }}-500)">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center text-xs text-zinc-500">
                <span>{{ __('Total Orders processed') }}</span>
                <span class="font-bold text-zinc-900 dark:text-white">{{ $stats['total_orders'] }}</span>
            </div>
        </flux:card>

        {{-- Recent Orders --}}
        <flux:card class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="md">{{ __('Recent Orders') }}</flux:heading>
                <flux:link href="{{ route('orders') }}" wire:navigate class="text-xs font-medium">{{ __('View All') }}</flux:link>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Order #') }}</flux:table.column>
                    <flux:table.column>{{ __('Customer') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Total') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($recentOrders as $order)
                        <flux:table.row>
                            <flux:table.cell class="font-mono font-bold">
                                <flux:link href="{{ route('orders.detail', ['id' => $order->id]) }}" wire:navigate>
                                    {{ $order->order_number }}
                                </flux:link>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="text-sm font-medium">{{ $order->customer->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $order->customer->phone }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$order->status->color()" size="sm">{{ $order->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="font-medium">{{ format_money($order->total_amount) }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    {{-- Third Row: Recent Payments --}}
    <div class="grid grid-cols-1 gap-8">
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="md">{{ __('Recent Payments') }}</flux:heading>
                <flux:icon name="banknotes" class="text-zinc-400 size-5" />
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Customer') }}</flux:table.column>
                    <flux:table.column>{{ __('Order #') }}</flux:table.column>
                    <flux:table.column>{{ __('Method') }}</flux:table.column>
                    <flux:table.column>{{ __('Amount') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($recentPayments as $payment)
                        <flux:table.row>
                            <flux:table.cell>{{ $payment->payment_date->format('d M, H:i') }}</flux:table.cell>
                            <flux:table.cell>{{ $payment->order->customer->name }}</flux:table.cell>
                            <flux:table.cell class="font-mono">
                                <flux:link href="{{ route('orders.detail', ['id' => $payment->order->id]) }}" wire:navigate>
                                    {{ $payment->order->order_number }}
                                </flux:link>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" variant="outline">{{ Str::headline($payment->payment_method) }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="font-bold text-emerald-600 dark:text-emerald-400">
                                {{ format_money($payment->amount) }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    {{-- Shared Components --}}
    @livewire('order.order-form')
</div>
