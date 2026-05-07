@extends('pdf.layout')

@section('title', 'Orders Summary')
@section('document_title', 'Orders Summary Report')

@section('content')
    <div style="margin-bottom: 20px;">
        @if($start_date || $end_date)
            <span>Period: <strong>{{ $start_date ?? 'Beginning' }}</strong> to <strong>{{ $end_date ?? 'Today' }}</strong></span>
        @else
            <span>All Time Report</span>
        @endif
    </div>

    <table class="info-grid" style="background: #fdfdfd; border: 1px solid #eee; padding: 15px;">
        <tr>
            <td>
                <div style="font-size: 10px; color: #666; text-transform: uppercase;">Total Sales</div>
                <div style="font-size: 18px; font-weight: bold;">{{ $currency_symbol }} {{ number_format($stats['total_sales'], 2) }}</div>
            </td>
            <td>
                <div style="font-size: 10px; color: #666; text-transform: uppercase;">Total Collected</div>
                <div style="font-size: 18px; font-weight: bold; color: #2d6a4f;">{{ $currency_symbol }} {{ number_format($stats['total_collected'], 2) }}</div>
            </td>
            <td class="text-right">
                <div style="font-size: 10px; color: #666; text-transform: uppercase;">Pending Payout</div>
                <div style="font-size: 18px; font-weight: bold; color: #c1121f;">{{ $currency_symbol }} {{ number_format($stats['total_pending'], 2) }}</div>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Status</th>
                <th class="text-right">Total</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Pending</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td class="font-bold">{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td>{{ $order->customer->name }}</td>
                    <td>{{ Str::headline($order->status->value) }}</td>
                    <td class="text-right">{{ $currency_symbol }} {{ number_format($order->total_amount, 2) }}</td>
                    <td class="text-right text-success">{{ $currency_symbol }} {{ number_format($order->paid_amount ?? 0, 2) }}</td>
                    <td class="text-right {{ $order->pending_amount > 0 ? 'text-red' : '' }}">
                        {{ $currency_symbol }} {{ number_format($order->pending_amount, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
