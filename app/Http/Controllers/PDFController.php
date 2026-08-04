<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payments;
use App\Services\OrderService;
use App\Services\CompanySettingService;
use App\Enums\CompanySettingKey;
use App\Enums\Currency;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function orderInvoice(Order $order)
    {
        $order->load(['customer', 'items.designFiles', 'payments']);
        
        $data = array_merge($this->getCompanyData(), [
            'order' => $order,
        ]);

        $pdf = Pdf::loadView('pdf.order-invoice', $data);
        return $pdf->stream("invoice-{$order->order_number}.pdf");
    }

    public function ordersSummary(Request $request, OrderService $service)
    {
        $search      = (string) $request->get('search', '');
        $phone       = $request->get('phone_number');
        $startDate   = $request->get('start_date');
        $endDate     = $request->get('end_date');
        $status      = $request->get('status');

        $orders = Order::query()
            ->with(['customer'])
            ->when($phone, fn($q) => $q->whereHas('customer', fn($q) => $q->where('phone', $phone)))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', '%' . $search . '%')
                        ->orWhereHas('customer', fn($q) => $q
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%')
                        );
                });
            })
            ->latest()
            ->get();

        $stats = $service->getStats($search, $phone, $startDate, $endDate, $status);

        $data = array_merge($this->getCompanyData(), [
            'orders' => $orders,
            'stats' => $stats,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $pdf = Pdf::loadView('pdf.orders-summary', $data);
        return $pdf->stream('orders-summary.pdf');
    }

    public function purchasesSummary(Request $request, \App\Services\PurchaseService $service)
    {
        $search      = (string) $request->get('search', '');
        $startDate   = $request->get('start_date');
        $endDate     = $request->get('end_date');
        $sortBy      = $request->get('sort', 'purchase_date');
        $sortDir     = $request->get('dir', 'desc');

        $purchases = \App\Models\Purchase::query()
            ->when($startDate, fn($q) => $q->whereDate('purchase_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('purchase_date', '<=', $endDate))
            ->when($search, function ($query) use ($search) {
                $query->where('item_name', 'like', '%' . $search . '%')
                      ->orWhere('notes', 'like', '%' . $search . '%');
            })
            ->orderBy($sortBy, $sortDir)
            ->get();

        $stats = $service->getStats($startDate, $endDate);

        $data = array_merge($this->getCompanyData(), [
            'purchases' => $purchases,
            'stats' => $stats,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $pdf = Pdf::loadView('pdf.purchases-summary', $data);
        return $pdf->stream('purchases-summary.pdf');
    }

    public function customerPayments(Customer $customer)
    {
        $orders = Order::where('customer_id', $customer->id)
            ->get()
            ->map(function ($order) {
                return (object)[
                    'type'         => 'order',
                    'date'         => $order->created_at,
                    'order_number' => $order->order_number,
                    'method'       => 'Order Billed',
                    'notes'        => $order->notes,
                    'debit'        => $order->effective_total,
                    'credit'       => 0,
                ];
            });

        $payments = Payments::whereHas('order', fn($q) => $q->where('customer_id', $customer->id))
            ->with('order')
            ->get()
            ->map(function ($payment) {
                return (object)[
                    'type'         => 'payment',
                    'date'         => $payment->payment_date,
                    'order_number' => $payment->order->order_number,
                    'method'       => 'Payment (' . \Illuminate\Support\Str::headline($payment->payment_method) . ')',
                    'notes'        => $payment->notes,
                    'debit'        => 0,
                    'credit'       => $payment->amount,
                ];
            });

        $transactions = $orders->concat($payments)->sortBy('date')->values();

        $data = array_merge($this->getCompanyData(), [
            'customer'     => $customer,
            'transactions' => $transactions,
        ]);

        $pdf = Pdf::loadView('pdf.customer-payments', $data);
        return $pdf->stream("payments-{$customer->name}.pdf");
    }

    private function getCompanyData(): array
    {
        $settings = app(CompanySettingService::class);
        
        return [
            'company_name'    => $settings->get(CompanySettingKey::NAME),
            'company_logo'    => $settings->get(CompanySettingKey::LOGO),
            'company_email'   => $settings->get(CompanySettingKey::EMAIL),
            'company_phone'   => $settings->get(CompanySettingKey::PHONE),
            'company_address' => $settings->get(CompanySettingKey::ADDRESS),
            'currency_symbol' => Currency::tryFrom($settings->get(CompanySettingKey::CURRENCY))->symbol(),
        ];
    }
}
