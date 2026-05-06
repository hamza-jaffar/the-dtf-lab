<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payments;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Record a new payment for an order.
     */
    public function recordPayment(Order $order, array $data): Payments
    {
        return DB::transaction(function () use ($order, $data) {
            $payment = Payments::create([
                'order_id'       => $order->id,
                'amount'         => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_date'   => $data['payment_date'] ?? now(),
                'notes'          => $data['notes'] ?? null,
            ]);

            // Update order's paid_amount
            $order->update([
                'paid_amount' => ($order->paid_amount ?? 0) + $data['amount']
            ]);

            return $payment;
        });
    }

    /**
     * Delete a payment and update the order's paid_amount.
     */
    public function deletePayment(Payments $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $order = $payment->order;
            $order->update([
                'paid_amount' => max(0, ($order->paid_amount ?? 0) - $payment->amount)
            ]);
            return $payment->delete();
        });
    }

    /**
     * Get payments for a specific order.
     */
    public function getOrderPayments(int $orderId)
    {
        return Payments::where('order_id', $orderId)->orderBy('payment_date', 'desc')->get();
    }
}
