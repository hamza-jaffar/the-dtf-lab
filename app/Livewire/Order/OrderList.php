<?php

namespace App\Livewire\Order;

use App\Enums\CompanySettingKey;
use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\CompanySettingService;
use App\Services\OrderService;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Orders')]
class OrderList extends Component
{
    use WithPagination;

    #[On('order-saved')]
    #[On('payment-saved')]
    public function refresh(): void
    {
        // Simply refresh the component
    }

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'phone_number')]
    public string $phoneFilter = '';

    public string $sortBy        = 'created_at';
    public string $sortDirection = 'desc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy        = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function updateStatus(int $orderId, string $status): void
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => $status]);
        Flux::toast(variant: 'success', text: 'Order status updated to ' . OrderStatus::from($status)->label() . '.');
    }

    public function deleteOrder(int $id, OrderService $service): void
    {
        $order = $service->find($id);
        $service->delete($order);
        Flux::toast(variant: 'success', text: 'Order deleted.');
    }

    public function render(OrderService $service)
    {
        $settings = app(CompanySettingService::class);
        $currency = $settings->get(CompanySettingKey::CURRENCY);
        $currencyEnum = Currency::tryFrom($currency);

        return view('livewire.order.order-list', [
            'orders'   => $service->getPaginated(
                search:         $this->search,
                sortBy:         $this->sortBy,
                sortDirection:  $this->sortDirection,
                phoneFilter:    $this->phoneFilter ?: null,
            ),
            'stats'    => $service->getStats(
                search:         $this->search,
                phoneFilter:    $this->phoneFilter ?: null,
            ),
            'statuses' => OrderStatus::cases(),
            'currency_symbol' => $currencyEnum->symbol(),
        ]);
    }
}
