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
                <th>Description</th>
                <th>Notes</th>
                <th class="text-right">Charge</th>
                <th class="text-right">Payment</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $balance = 0; @endphp
            @forelse($transactions as $transaction)
                @php 
                    $balance += $transaction->debit;
                    $balance -= $transaction->credit;
                @endphp
                <tr>
                    <td>{{ $transaction->date->format('d M Y H:i') }}</td>
                    <td class="font-bold">{{ $transaction->order_number }}</td>
                    <td>{{ $transaction->method }}</td>
                    <td>{{ $transaction->notes }}</td>
                    <td class="text-right" style="color: #c1121f;">{{ $transaction->debit > 0 ? $currency_symbol . ' ' . number_format($transaction->debit, 2) : '-' }}</td>
                    <td class="text-right text-success">{{ $transaction->credit > 0 ? $currency_symbol . ' ' . number_format($transaction->credit, 2) : '-' }}</td>
                    <td class="text-right font-bold">{{ $currency_symbol }} {{ number_format($balance, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No transaction history found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer" style="margin-top: 100px; border-top: 1px solid #eee; padding-top: 20px;">
        This is a computer-generated statement and does not require a signature.
    </div>
@endsection
