<?php

namespace App\Livewire\Order;

use App\Enums\CompanySettingKey;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Services\CompanySettingService;
use App\Services\OrderService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderForm extends Component
{
    use WithFileUploads;

    // ------------------------------------------------------------------
    // Order-level fields
    // ------------------------------------------------------------------
    public ?int   $orderId    = null;
    public ?int   $customerId = null;
    public string $status     = 'pending';
    public string $type       = 'dtf';
    public ?float $paidAmount = null;
    public string $notes      = '';

    // ------------------------------------------------------------------
    // Publicly tracked default rate (survives Livewire hydration)
    // ------------------------------------------------------------------
    public float $defaultRate = 0;

    // ------------------------------------------------------------------
    // Line items — array of rows managed in-component
    // ------------------------------------------------------------------
    public array $items = [];

    public bool $isEditing = false;

    // ------------------------------------------------------------------
    // Lifecycle
    // ------------------------------------------------------------------
    public function mount(?int $customerId = null): void
    {
        $settings          = app(CompanySettingService::class);
        $this->customerId  = $customerId;
        $this->defaultRate = (float) ($settings->get(CompanySettingKey::DEFAULT_RATE) ?? 0);
        $this->addItem();
    }

    // ------------------------------------------------------------------
    // Open / Load
    // ------------------------------------------------------------------

    #[On('open-order-form')]
    public function openForCreate(?int $customerId = null): void
    {
        $settings = app(CompanySettingService::class);
        $this->resetAll();
        
        $this->customerId  = $customerId;
        $this->defaultRate = (float) ($settings->get(CompanySettingKey::DEFAULT_RATE) ?? 0);
        
        // Always start with one empty item pre-filled with the default rate
        $this->addItem();
        
        Flux::modal('order-form-modal')->show();
    }

    #[On('edit-order')]
    public function openForEdit(int $orderId, OrderService $service, CompanySettingService $settings): void
    {
        $this->resetAll();
        $order = $service->find($orderId);

        $this->defaultRate = (float) ($settings->get(CompanySettingKey::DEFAULT_RATE) ?? 0);
        $this->orderId     = $order->id;
        $this->items       = [];
        $this->customerId  = $order->customer_id;
        $this->status      = $order->status->value;
        $this->type        = $order->type ?? 'dtf';
        $this->paidAmount  = (float) $order->paid_amount;
        $this->notes       = $order->notes ?? '';
        $this->isEditing   = true;

        foreach ($order->items as $item) {
            $this->items[] = [
                'pricing_type'   => $item->pricing_type ?? 'per_sqin',
                'width'          => (string) $item->width,
                'height'         => (string) $item->height,
                'rate_per_inch'  => (string) $item->rate_per_inch,
                'quantity'       => $item->quantity,
                'design_name'    => $item->design_name ?? '',
                'files'          => [],
                'existing_files' => $item->designFiles->map(fn($f) => [
                    'id'   => $f->id,
                    'name' => $f->original_name,
                    'url'  => $f->url,
                ])->toArray(),
            ];
        }

        Flux::modal('order-form-modal')->show();
    }

    // ------------------------------------------------------------------
    // Item management
    // ------------------------------------------------------------------
    public function addItem(): void
    {
        $this->items[] = [
            'pricing_type'   => 'per_sqin',
            'width'          => '',
            'height'         => '',
            'rate_per_inch'  => $this->defaultRate > 0 ? (string) $this->defaultRate : '',
            'quantity'       => 1,
            'design_name'    => '',
            'files'          => [],
            'existing_files' => [],
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    // ------------------------------------------------------------------
    // Validation
    // ------------------------------------------------------------------
    public function rules(): array
    {
        $rules = [
            'customerId' => 'required|exists:customers,id',
            'status'     => 'required|in:' . implode(',', array_column(OrderStatus::cases(), 'value')),
            'type'       => 'required|in:dtf,dtg,screen_printing,reflector_vinyl,embroidery,sublimation,rhinestone',
            'notes'      => 'nullable|string|max:1000',
            'items'      => 'required|array|min:1',
        ];

        foreach ($this->items as $i => $item) {
            $isPricedByArea = ($item['pricing_type'] ?? 'per_sqin') === 'per_sqin';

            $rules["items.{$i}.pricing_type"] = 'required|in:per_sqin,per_piece';
            $rules["items.{$i}.width"]         = $isPricedByArea ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0';
            $rules["items.{$i}.height"]        = $isPricedByArea ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0';
            $rules["items.{$i}.rate_per_inch"] = 'required|numeric|min:0.01';
            $rules["items.{$i}.quantity"]      = 'required|integer|min:1';
            $rules["items.{$i}.design_name"]   = 'nullable|string|max:255';
            $rules["items.{$i}.files"]         = 'nullable|array';
            $rules["items.{$i}.files.*"]       = 'nullable|file|mimes:png,jpg,jpeg,pdf,ai,svg|max:10240';
        }

        return $rules;
    }

    // ------------------------------------------------------------------
    // Save
    // ------------------------------------------------------------------
    public function save(OrderService $service): void
    {
        $this->items = $this->normalizeItems($this->items);

        if ($this->isEditing && blank($this->orderId)) {
            Flux::toast(variant: 'error', text: 'Unable to update this order because the order reference is missing.');
            return;
        }

        $this->validate();

        $orderData = [
            'customer_id' => (int) $this->customerId,
            'status'      => (string) $this->status,
            'type'        => (string) $this->type,
            'paid_amount' => $this->paidAmount !== null && $this->paidAmount !== '' ? (float) $this->paidAmount : 0,
            'notes'       => $this->notes ?: null,
        ];

        if ($this->isEditing) {
            $order = $service->find($this->orderId);
            $service->update($order, $orderData, $this->items);
            $message = 'Order updated successfully.';
        } else {
            $service->create($orderData, $this->items);
            $message = 'Order created successfully.';
        }

        Flux::modal('order-form-modal')->close();
        Flux::toast(variant: 'success', text: $message);
        $this->dispatch('order-saved');
        $this->resetAll();
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------
    private function normalizeItems(array $items): array
    {
        return array_values(array_map(function (array $item): array {
            return [
                'pricing_type'   => $item['pricing_type'] ?? 'per_sqin',
                'width'          => $item['width'] ?? '',
                'height'         => $item['height'] ?? '',
                'rate_per_inch'  => $item['rate_per_inch'] ?? '',
                'quantity'       => (int) ($item['quantity'] ?? 1),
                'design_name'    => $item['design_name'] ?? '',
                'files'          => $item['files'] ?? [],
                'existing_files' => $item['existing_files'] ?? [],
            ];
        }, $items));
    }

    private function resetAll(): void
    {
        $this->reset(['orderId', 'customerId', 'status', 'type', 'paidAmount', 'notes', 'items', 'isEditing']);
        $this->status = 'pending';
        $this->type = 'dtf';
        $this->resetValidation();
        
        // Re-fetch default rate to ensure it's current
        $settings = app(CompanySettingService::class);
        $this->defaultRate = (float) ($settings->get(CompanySettingKey::DEFAULT_RATE) ?? 0);
    }

    public function render()
    {
        return view('livewire.order.order-form', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
            'statuses'  => OrderStatus::cases(),
        ]);
    }
}
