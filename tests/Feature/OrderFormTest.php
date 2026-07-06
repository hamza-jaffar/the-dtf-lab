<?php

use App\Enums\OrderStatus;
use App\Livewire\Order\OrderForm;
use App\Models\Customer;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('updates an existing order from the form component', function () {
    $customer = Customer::create([
        'name' => 'Test Customer',
        'phone' => '1234567890',
        'email' => 'customer@example.com',
        'address' => 'Test Address',
    ]);

    $order = Order::create([
        'order_number' => 'ORD-00001',
        'customer_id' => $customer->id,
        'status' => OrderStatus::Pending->value,
        'total_amount' => 100,
        'paid_amount' => 0,
        'notes' => 'old notes',
    ]);

    $order->items()->create([
        'width' => 10,
        'height' => 20,
        'square_inches' => 200,
        'rate_per_inch' => 2,
        'total_price' => 400,
        'design_name' => 'old design',
        'quantity' => 1,
        'pricing_type' => 'per_sqin',
    ]);

    Livewire::test(OrderForm::class)
        ->set('isEditing', true)
        ->set('orderId', $order->id)
        ->set('customerId', $customer->id)
        ->set('status', OrderStatus::Completed->value)
        ->set('paidAmount', 50)
        ->set('notes', 'updated notes')
        ->set('items', [[
            'pricing_type' => 'per_sqin',
            'width' => '10',
            'height' => '20',
            'rate_per_inch' => '3',
            'quantity' => 2,
            'design_name' => 'updated design',
            'files' => [],
            'existing_files' => [],
        ]])
        ->call('save', app(OrderService::class));

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->customer_id)->toBe($customer->id)
        ->and((float) $order->paid_amount)->toBe(50.0)
        ->and($order->notes)->toBe('updated notes')
        ->and($order->items)->toHaveCount(1);
});
