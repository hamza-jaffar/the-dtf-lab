@extends('pdf.layout')

@section('title', 'Purchases Summary')
@section('document_title', 'Purchases Summary Report')

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
                <div style="font-size: 10px; color: #666; text-transform: uppercase;">Total Spent</div>
                <div style="font-size: 18px; font-weight: bold; color: #c1121f;">{{ $currency_symbol }} {{ number_format($stats['total_spent'], 2) }}</div>
            </td>
            <td>
                <div style="font-size: 10px; color: #666; text-transform: uppercase;">Total Items Bought</div>
                <div style="font-size: 18px; font-weight: bold;">{{ number_format($stats['total_items']) }}</div>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Date</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $purchase)
                <tr>
                    <td class="font-bold">{{ $purchase->item_name }}</td>
                    <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                    <td class="text-right">{{ $purchase->quantity }}</td>
                    <td class="text-right">{{ $currency_symbol }} {{ number_format($purchase->unit_price, 2) }}</td>
                    <td class="text-right font-bold">{{ $currency_symbol }} {{ number_format($purchase->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
