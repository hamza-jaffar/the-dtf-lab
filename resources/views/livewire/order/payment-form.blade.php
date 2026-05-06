<flux:modal name="payment-modal" class="md:w-[500px] space-y-6">
    <div>
        <flux:heading size="lg">{{ __('Record Payment') }}</flux:heading>
        <flux:subheading>{{ __('Enter the payment details for this order.') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="space-y-4">
            @if($customerId && !$orderId)
                <flux:select wire:model="orderId" :label="__('Select Order')">
                    <flux:select.option value="">{{ __('— Choose an order to pay —') }}</flux:select.option>
                    @foreach($this->orders as $o)
                        <flux:select.option :value="$o->id">
                            {{ $o->order_number }} - Total: {{ format_money($o->total_amount) }} (Pending: {{ format_money($o->pending_amount) }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
            @endif

            <flux:input 
                wire:model="amount" 
                :label="__('Amount')" 
                type="number" 
                step="any" 
                required 
                icon="banknotes"
            />

            <flux:select wire:model="paymentMethod" :label="__('Payment Method')">
                @foreach($methods as $method)
                    <flux:select.option :value="$method->value">{{ $method->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input 
                wire:model="paymentDate" 
                :label="__('Payment Date')" 
                type="datetime-local" 
                required 
            />

            <flux:textarea 
                wire:model="notes" 
                :label="__('Notes')" 
                placeholder="{{ __('Optional payment notes...') }}" 
                rows="2" 
            />
        </div>

        <div class="flex justify-end space-x-2">
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button variant="primary" type="submit">
                {{ __('Save Payment') }}
            </flux:button>
        </div>
    </form>
</flux:modal>
