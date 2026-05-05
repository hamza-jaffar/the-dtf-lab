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
    public function openForCustomer(int $customerId, CompanySettingService $settings): void
    {
        $this->resetAll();
        $this->customerId  = $customerId;
        $this->defaultRate = (float) ($settings->get(CompanySettingKey::DEFAULT_RATE) ?? 0);
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
        $this->customerId  = $order->customer_id;
        $this->status      = $order->status->value;
        $this->notes       = $order->notes ?? '';
        $this->isEditing   = true;

        foreach ($order->items as $item) {
            $this->items[] = [
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
            'notes'      => 'nullable|string|max:1000',
            'items'      => 'required|array|min:1',
        ];

        foreach ($this->items as $i => $item) {
            $rules["items.{$i}.width"]         = 'required|numeric|min:0.01';
            $rules["items.{$i}.height"]        = 'required|numeric|min:0.01';
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
        $this->validate();

        $orderData = [
            'customer_id' => $this->customerId,
            'status'      => $this->status,
            'notes'       => $this->notes,
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
    private function resetAll(): void
    {
        $this->reset(['orderId', 'customerId', 'status', 'notes', 'items', 'isEditing']);
        $this->status = 'pending';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.order.order-form', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
            'statuses'  => OrderStatus::cases(),
        ]);
    }
}
