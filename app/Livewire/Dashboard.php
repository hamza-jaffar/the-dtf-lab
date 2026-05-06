<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payments;
use App\Enums\OrderStatus;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_sales'      => Order::sum('total_amount'),
            'total_collected'  => Order::sum('paid_amount'),
            'total_pending'    => Order::selectRaw('SUM(total_amount - COALESCE(paid_amount, 0)) as pending')->value('pending') ?? 0,
            'total_customers'  => Customer::count(),
            'total_orders'     => Order::count(),
            'completed_orders' => Order::where('status', OrderStatus::Completed->value)->count(),
        ];

        $recentOrders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get();

        $recentPayments = Payments::with('order.customer')
            ->latest()
            ->take(5)
            ->get();

        $statusDistribution = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status->value => $item->count]);

        return view('livewire.dashboard', [
            'stats'              => $stats,
            'recentOrders'       => $recentOrders,
            'recentPayments'     => $recentPayments,
            'statusDistribution' => $statusDistribution,
            'statuses'           => OrderStatus::cases(),
        ]);
    }
}
