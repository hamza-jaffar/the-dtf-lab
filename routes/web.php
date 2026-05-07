<?php

use App\Livewire\Customer\Customer;
use App\Livewire\Order\OrderList;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', \App\Livewire\Dashboard::class)->name('dashboard');
    Route::livewire('customers', Customer::class)->name('customers');
    Route::livewire('orders', OrderList::class)->name('orders');
    Route::livewire('orders/{id}', \App\Livewire\Order\OrderDetail::class)->name('orders.detail');

    Route::get('orders/{order}/invoice', [\App\Http\Controllers\PDFController::class, 'orderInvoice'])->name('orders.invoice.pdf');
    Route::get('orders/export/pdf', [\App\Http\Controllers\PDFController::class, 'ordersSummary'])->name('orders.export.pdf');
    Route::get('customers/{customer}/payments', [\App\Http\Controllers\PDFController::class, 'customerPayments'])->name('customers.payments.pdf');
});

require __DIR__.'/settings.php';
