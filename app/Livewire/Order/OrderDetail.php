<?php

namespace App\Livewire\Order;

use App\Models\Order;
use App\Services\OrderService;
use App\Enums\OrderStatus;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

#[Title('Order Details')]
class OrderDetail extends Component
{
    public Order $order;

    /** Holds the manual total override value (bound to the input in the view). */
    public ?float $manualTotal = null;

    public function mount(int $id, OrderService $service)
    {
        $this->order = $service->find($id);
        $this->manualTotal = $this->order->price_override;
    }

    public function updateStatus(string $status)
    {
        $this->order->update(['status' => $status]);
        $this->order->refresh();
        Flux::toast(variant: 'success', text: 'Order status updated to ' . OrderStatus::from($status)->label() . '.');
    }

    /**
     * Save the manual price override entered by the user.
     * Pass null to clear the override and revert to the calculated total.
     */
    public function savePriceOverride(OrderService $service)
    {
        $this->validate([
            'manualTotal' => 'nullable|numeric|min:0',
        ]);

        $service->updatePriceOverride($this->order, $this->manualTotal ?: null);
        $this->order->refresh();

        $msg = $this->manualTotal
            ? 'Price overridden to ' . number_format($this->manualTotal, 2) . '.'
            : 'Price override cleared — using calculated total.';

        Flux::toast(variant: 'success', text: $msg);
    }

    /** Re-load the order after the edit form saves it. */
    #[On('order-saved')]
    public function onOrderSaved(OrderService $service)
    {
        $this->order = $service->find($this->order->id);
        $this->manualTotal = $this->order->price_override;
    }

    /**
     * Clear the price override and revert to the calculated total.
     */
    public function clearPriceOverride(OrderService $service)
    {
        $this->manualTotal = null;
        $service->updatePriceOverride($this->order, null);
        $this->order->refresh();
        Flux::toast(variant: 'success', text: 'Price override cleared — using calculated total.');
    }

    public function render()
    {
        return view('livewire.order.order-detail', [
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
