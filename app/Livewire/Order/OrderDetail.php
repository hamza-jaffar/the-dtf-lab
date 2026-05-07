<?php

namespace App\Livewire\Order;

use App\Models\Order;
use App\Services\OrderService;
use App\Enums\OrderStatus;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

#[Title('Order Details')]
class OrderDetail extends Component
{
    public Order $order;

    public function mount(int $id, OrderService $service)
    {
        $this->order = $service->find($id);
    }

    public function updateStatus(string $status)
    {
        $this->order->update(['status' => $status]);
        $this->order->refresh();
        Flux::toast(variant: 'success', text: 'Order status updated to ' . OrderStatus::from($status)->label() . '.');
    }

    public function render()
    {
        return view('livewire.order.order-detail', [
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
