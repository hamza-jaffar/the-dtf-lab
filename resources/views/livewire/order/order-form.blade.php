<flux:modal name="order-form-modal" class="md:w-[780px] space-y-6" wire:ignore.self>

    {{-- Header --}}
    <div>
        <flux:heading size="lg">
            {{ $isEditing ? __('Edit Order') : __('New Order') }}
        </flux:heading>
        <flux:subheading>
            {{ __('Fill in order details and add line items below.') }}
        </flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- Order Meta --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Customer select --}}
            <div class="md:col-span-2">
                <flux:select wire:model="customerId" :label="__('Customer')">
                    <flux:select.option value="">{{ __('— Select a customer —') }}</flux:select.option>
                    @foreach ($customers as $c)
                        <flux:select.option :value="$c->id">{{ $c->name }} ({{ $c->phone }})</flux:select.option>
                    @endforeach
                </flux:select>
                @error('customerId')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <flux:select wire:model="status" :label="__('Status')">
                @foreach ($statuses as $s)
                    <flux:select.option :value="$s->value">{{ $s->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <flux:textarea wire:model="notes" :label="__('Notes')" rows="2"
                       placeholder="{{ __('Optional notes about this order…') }}" />

        {{-- ================================================================
             Line Items — Alpine handles real-time calculations client-side
             ================================================================ --}}
        <div
            x-data="{
                items: $wire.entangle('items'),
                defaultRate: {{ $defaultRate }},

                get grandTotal() {
                    return this.items.reduce((sum, item) => {
                        const w = parseFloat(item.width)  || 0;
                        const h = parseFloat(item.height) || 0;
                        const r = parseFloat(item.rate_per_inch) || 0;
                        const q = parseInt(item.quantity)   || 1;
                        return sum + (w * h * r * q);
                    }, 0);
                },

                lineTotal(item) {
                    const w = parseFloat(item.width)  || 0;
                    const h = parseFloat(item.height) || 0;
                    const r = parseFloat(item.rate_per_inch) || 0;
                    const q = parseInt(item.quantity)   || 1;
                    return (w * h * r * q).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                },

                squareInches(item) {
                    const w = parseFloat(item.width)  || 0;
                    const h = parseFloat(item.height) || 0;
                    return (w * h).toLocaleString(undefined, { maximumFractionDigits: 2 });
                },

                grandTotalFormatted() {
                    return this.grandTotal.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                }
            }"
        >
            {{-- Section Header --}}
            <div class="flex items-center justify-between mb-3">
                <flux:heading size="sm">{{ __('Line Items') }}</flux:heading>
                <flux:button type="button" variant="ghost" size="sm" icon="plus"
                             wire:click="addItem">{{ __('Add Item') }}</flux:button>
            </div>

            {{-- Items --}}
            <div class="space-y-4">
                @foreach ($items as $i => $item)
                    <div class="relative rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-4"
                         wire:key="item-{{ $i }}"
                         x-data>

                        {{-- Remove button --}}
                        @if (count($items) > 1)
                            <flux:button type="button" variant="ghost" size="xs" icon="x-mark" color="danger"
                                         class="absolute top-2 right-2"
                                         wire:click="removeItem({{ $i }})" />
                        @endif

                        {{-- Dimension + rate inputs --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div>
                                <flux:input
                                    wire:model.live="items.{{ $i }}.width"
                                    :label="__('Width (in)')"
                                    type="number" min="0.01" step="any" placeholder="10" />
                                @error("items.{$i}.width")
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <flux:input
                                    wire:model.live="items.{{ $i }}.height"
                                    :label="__('Height (in)')"
                                    type="number" min="0.01" step="any" placeholder="12" />
                                @error("items.{$i}.height")
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <flux:input
                                    wire:model.live="items.{{ $i }}.rate_per_inch"
                                    :label="__('Rate / in²')"
                                    type="number"
                                    min="0.01"
                                    step="any"
                                    placeholder="e.g. 2.5" />
                                @error("items.{$i}.rate_per_inch")
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <flux:input
                                    wire:model.live="items.{{ $i }}.quantity"
                                    :label="__('Qty')"
                                    type="number" min="1" placeholder="1" />
                                @error("items.{$i}.quantity")
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Design name + live subtotal --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
                            <flux:input
                                wire:model="items.{{ $i }}.design_name"
                                :label="__('Design Name')"
                                placeholder="e.g. Logo Print" />

                            {{-- Real-time subtotal (Alpine renders this) --}}
                            <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800/60 px-4 py-2.5">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-0.5">{{ __('Subtotal') }}</p>
                                <p class="text-base font-bold text-zinc-900 dark:text-white">
                                    <span x-text="lineTotal(items[{{ $i }}])">—</span>
                                </p>
                                <p class="text-xs text-zinc-400 mt-0.5">
                                    <span x-text="squareInches(items[{{ $i }}])">0</span>
                                    {{ __('in²') }} × rate × qty
                                </p>
                            </div>
                        </div>

                        {{-- Design file upload --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                {{ __('Design Files') }}
                                <span class="text-xs font-normal text-zinc-400">
                                    (PNG, JPG, PDF, AI, SVG — max 10MB each)
                                </span>
                            </label>
                            <input type="file" multiple
                                   accept=".png,.jpg,.jpeg,.pdf,.ai,.svg"
                                   wire:model="items.{{ $i }}.files"
                                   class="block w-full text-sm text-zinc-500
                                          file:mr-4 file:py-1.5 file:px-3
                                          file:rounded-lg file:border-0
                                          file:text-sm file:font-medium
                                          file:bg-zinc-100 file:text-zinc-700
                                          hover:file:bg-zinc-200
                                          dark:file:bg-zinc-800 dark:file:text-zinc-300
                                          dark:hover:file:bg-zinc-700" />
                            @error("items.{$i}.files.*")
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror

                            {{-- Existing files on edit --}}
                            @if (!empty($item['existing_files']))
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($item['existing_files'] as $ef)
                                        <a href="{{ $ef['url'] }}" target="_blank"
                                           class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 px-2 py-1 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                                            <flux:icon name="paper-clip" class="size-3" />
                                            {{ $ef['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- Grand Total banner --}}
            <div class="mt-4 rounded-xl bg-zinc-900 dark:bg-zinc-700 px-5 py-4 flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-300">{{ __('Grand Total') }}</span>
                <span class="text-2xl font-bold text-white tracking-tight"
                      x-text="grandTotalFormatted()">0</span>
            </div>

        </div>{{-- end x-data --}}

        {{-- Actions --}}
        <div class="flex justify-end gap-2 pt-2">
            <flux:modal.close>
                <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" type="submit">
                {{ $isEditing ? __('Update Order') : __('Create Order') }}
            </flux:button>
        </div>

    </form>
</flux:modal>
