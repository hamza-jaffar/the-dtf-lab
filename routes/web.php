<?php

use App\Livewire\Customer\Customer;
use App\Livewire\Order\OrderList;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('customers', Customer::class)->name('customers');
    Route::livewire('orders', OrderList::class)->name('orders');
});

require __DIR__.'/settings.php';
