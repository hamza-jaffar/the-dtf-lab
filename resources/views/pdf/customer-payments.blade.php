@extends('pdf.layout')

@section('title', 'Payments - ' . $customer->name)
@section('document_title', 'Customer Payment Ledger')

@section('content')
    <table class="info-grid">
        <tr>
            <td>
                <div class="font-bold">Customer:</div>
                <div>{{ $customer->name }}</div>
                <div>{{ $customer->phone }}</div>
                @if($customer->email)<div>{{ $customer->email }}</div>@endif
            </td>
            <td class="text-right">
                <div class="font-bold">Account Summary:</div>
                <div>Total Billed: {{ $currency_symbol }} {{ number_format($customer->orders_sum_total_amount ?? $customer->orders()->sum('total_amount'), 2) }}</div>
                <div style="color: #2d6a4f;">Total Paid: {{ $currency_symbol }} {{ number_format($customer->orders_sum_paid_amount ?? $customer->orders()->sum('paid_amount'), 2) }}</div>
                <div style="color: #c1121f; font-weight: bold;">Outstanding: {{ $currency_symbol }} {{ number_format(($customer->orders()->sum('total_amount') - $customer->orders()->sum('paid_amount')), 2) }}</div>
            </td>
        </tr>
    </table>

    <div class="mt-10 font-bold">Transaction History:</div>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Order #</th>
                <th>Method</th>
                <th>Notes</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('d M Y H:i') }}</td>
                    <td class="font-bold">{{ $payment->order->order_number }}</td>
                    <td>{{ Str::headline($payment->payment_method) }}</td>
                    <td>{{ $payment->notes }}</td>
                    <td class="text-right font-bold text-success">{{ $currency_symbol }} {{ number_format($payment->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer" style="margin-top: 100px; border-top: 1px solid #eee; padding-top: 20px;">
        This is a computer-generated statement and does not require a signature.
    </div>
@endsection
