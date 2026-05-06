<?php

namespace App\Livewire\Order;

use App\Models\Order;
use App\Services\PaymentService;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class PaymentForm extends Component
{
    public ?int $orderId = null;
    public ?int $customerId = null;
    public $amount;
    public $paymentMethod = 'cash';
    public $paymentDate;
    public $notes;

    public function mount()
    {
        $this->paymentDate = now()->format('Y-m-d\TH:i');
    }

    public function updatedOrderId($value)
    {
        if ($value) {
            $order = Order::find($value);
            $this->amount = $order->total_amount - ($order->paid_amount ?? 0);
        }
    }

    #[On('open-payment-modal')]
    public function openModal(?int $orderId = null, ?int $customerId = null)
    {
        $this->reset(['amount', 'notes', 'orderId', 'customerId']);
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        
        if ($orderId) {
            $order = Order::find($orderId);
            $this->amount = $order->total_amount - ($order->paid_amount ?? 0);
        }
        
        $this->paymentDate = now()->format('Y-m-d\TH:i');
        
        Flux::modal('payment-modal')->show();
    }

    public function getOrdersProperty()
    {
        if (!$this->customerId) {
            return [];
        }

        return Order::where('customer_id', $this->customerId)
            ->where(function($q) {
                $q->whereRaw('total_amount > paid_amount')
                  ->orWhereNull('paid_amount');
            })
            ->get();
    }

    public function save(PaymentService $service)
    {
        $this->validate([
            'orderId'       => 'required|exists:orders,id',
            'amount'        => 'required|numeric|min:1',
            'paymentMethod' => 'required|string',
            'paymentDate'   => 'required',
            'notes'         => 'nullable|string',
        ]);

        $order = Order::find($this->orderId);
        
        $service->recordPayment($order, [
            'amount'         => $this->amount,
            'payment_method' => $this->paymentMethod,
            'payment_date'   => $this->paymentDate,
            'notes'          => $this->notes,
        ]);

        Flux::modal('payment-modal')->close();
        Flux::toast(variant: 'success', text: 'Payment recorded successfully.');
        
        $this->dispatch('payment-saved');
    }

    public function render()
    {
        return view('livewire.order.payment-form', [
            'methods' => \App\Enums\Payment::cases(),
        ]);
    }
}
