<?php

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows earnings cards for each order type', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::create([
        'name' => 'Demo Customer',
        'phone' => '1234567890',
        'email' => 'demo@example.com',
        'address' => '123 Demo Street',
    ]);
    Order::create([
        'order_number' => 'ORD-1001',
        'customer_id' => $customer->id,
        'status' => OrderStatus::Pending->value,
        'total_amount' => 1250,
        'paid_amount' => 0,
        'type' => 'dtf',
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Earnings by Type');
    $response->assertSee('DTF');
    $response->assertSee('Paid');
    $response->assertSee('Pending');
});