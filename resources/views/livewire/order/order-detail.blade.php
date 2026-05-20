<section class="w-full space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <flux:button icon="chevron-left" variant="subtle" href="{{ route('orders') }}" wire:navigate />
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="xl">{{ __('Order') }} #{{ $order->order_number }}</flux:heading>
                    <flux:badge :color="$order->status->color()" size="sm">{{ $order->status->label() }}</flux:badge>
                </div>
                <flux:subheading>{{ __('Placed on') }} {{ $order->created_at->format('d M Y, H:i') }}</flux:subheading>
            </div>
        </div>
        <div class="flex gap-2">
            <flux:button icon="document-text" variant="primary"
                href="{{ route('orders.invoice.pdf', ['order' => $order->id]) }}" target="_blank">
                {{ __('Download Invoice') }}
            </flux:button>
            <flux:button icon="pencil" variant="subtle" wire:click="$dispatch('edit-order', { orderId: {{ $order->id }} })">
                {{ __('Edit Order') }}
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Order Info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Items Card --}}
            <flux:card class="space-y-4">
                <flux:heading size="md">{{ __('Order Items') }}</flux:heading>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Design') }}</flux:table.column>
                        <flux:table.column>{{ __('Size (WxH)') }}</flux:table.column>
                        <flux:table.column>{{ __('Price') }}</flux:table.column>
                        <flux:table.column>{{ __('Qty') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Subtotal') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($order->items as $item)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $item->design_name ?? 'N/A' }}</div>
                                    @if($item->designFiles->count() > 0)
                                        <div class="flex gap-1 mt-1 flex-wrap">
                                            @foreach($item->designFiles as $file)
                                                <flux:link href="{{ Storage::url($file->file_path) }}" target="_blank" class="text-[10px] bg-zinc-100 dark:bg-zinc-800 px-1 rounded">
                                                    {{ Str::limit($file->original_name, 15) }}
                                                </flux:link>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-[10px] text-zinc-500 mt-1">{{ __('No file uploaded') }}</div>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    {{ $item->width }}" x {{ $item->height }}"
                                    <div class="text-xs text-zinc-500">{{ $item->square_inches }} {{ __('sq.in') }}</div>
                                </flux:table.cell>
                                <flux:table.cell>{{ format_money($item->rate_per_inch) }}/in</flux:table.cell>
                                <flux:table.cell>{{ $item->quantity }}</flux:table.cell>
                                <flux:table.cell class="text-right font-bold">{{ format_money($item->total_price) }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                <div class="border-t border-zinc-100 dark:border-zinc-800 pt-4 flex flex-col items-end gap-2">
                    <div class="flex justify-between w-full max-w-xs text-sm">
                        <span class="text-zinc-500">{{ __('Total Amount') }}:</span>
                        <span class="font-bold text-zinc-900 dark:text-white">{{ format_money($order->total_amount) }}</span>
                    </div>
                    <div class="flex justify-between w-full max-w-xs text-sm">
                        <span class="text-zinc-500">{{ __('Paid Amount') }}:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ format_money($order->paid_amount ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between w-full max-w-xs border-t border-zinc-100 dark:border-zinc-800 pt-2 text-base">
                        <span class="font-bold text-zinc-900 dark:text-white">{{ __('Pending Balance') }}:</span>
                        <span class="font-bold text-red-500">{{ format_money($order->pending_amount) }}</span>
                    </div>
                </div>
            </flux:card>

            {{-- Payments Card --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="md">{{ __('Payment History') }}</flux:heading>
                    <flux:button size="sm" icon="plus" variant="subtle" wire:click="$dispatch('open-payment-modal', { orderId: {{ $order->id }} })">
                        {{ __('Record Payment') }}
                    </flux:button>
                </div>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Method') }}</flux:table.column>
                        <flux:table.column>{{ __('Notes') }}</flux:table.column>
                        <flux:table.column class="text-right">{{ __('Amount') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($order->payments as $payment)
                            <flux:table.row>
                                <flux:table.cell>{{ $payment->payment_date->format('d M Y, H:i') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" variant="outline">{{ Str::headline($payment->payment_method) }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="max-w-xs truncate">{{ $payment->notes ?: '-' }}</flux:table.cell>
                                <flux:table.cell class="text-right font-bold text-emerald-600">{{ format_money($payment->amount) }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center text-zinc-500 py-4">{{ __('No payments recorded yet.') }}</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>

        {{-- Sidebar Info --}}
        <div class="space-y-6">
            {{-- Customer Card --}}
            <flux:card class="space-y-4">
                <flux:heading size="md">{{ __('Customer Info') }}</flux:heading>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-zinc-600 dark:text-zinc-400">
                            {{ Str::substr($order->customer->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-zinc-900 dark:text-white">{{ $order->customer->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $order->customer->phone }}</div>
                        </div>
                    </div>
                    <flux:separator />
                    <div class="space-y-2">
                        @if($order->customer->email)
                            <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon name="envelope" class="size-4" />
                                {{ $order->customer->email }}
                            </div>
                        @endif
                        @if($order->customer->address)
                            <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <flux:icon name="map-pin" class="size-4" />
                                {{ $order->customer->address }}
                            </div>
                        @endif
                    </div>
                    <flux:button variant="subtle" size="sm" class="w-full" href="{{ route('customers') }}" wire:navigate>
                        {{ __('View Customer Profile') }}
                    </flux:button>
                </div>
            </flux:card>

            {{-- Status Management Card --}}
            <flux:card class="space-y-4">
                <flux:heading size="md">{{ __('Manage Order') }}</flux:heading>
                <div class="space-y-4">
                    <div>
                        <flux:label>{{ __('Update Status') }}</flux:label>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            @foreach($statuses as $status)
                                <flux:button
                                    size="sm"
                                    variant="{{ $order->status === $status ? 'filled' : 'subtle' }}"
                                    color="{{ $order->status === $status ? $status->color() : '' }}"
                                    wire:click="updateStatus('{{ $status->value }}')"
                                    class="justify-start"
                                >
                                    {{ $status->label() }}
                                </flux:button>
                            @endforeach
                        </div>
                    </div>

                    @if($order->notes)
                        <flux:separator />
                        <div>
                            <flux:label>{{ __('Order Notes') }}</flux:label>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1 italic">
                                "{{ $order->notes }}"
                            </p>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>
    </div>

    @livewire('order.order-form')
    @livewire('order.payment-form')
</section>
