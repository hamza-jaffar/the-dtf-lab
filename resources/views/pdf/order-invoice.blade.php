@extends('pdf.layout')

@section('title', 'Invoice ' . $order->order_number)
@section('document_title', 'Order Invoice')

@section('content')
    <table class="info-grid">
        <tr>
            <td>
                <div class="font-bold">Billed To:</div>
                <div>{{ $order->customer->name }}</div>
                <div>{{ $order->customer->phone }}</div>
                @if($order->customer->email)<div>{{ $order->customer->email }}</div>@endif
                @if($order->customer->address)<div>{{ $order->customer->address }}</div>@endif
            </td>
            <td class="text-right">
                <div class="font-bold">Order Details:</div>
                <div>Order #: {{ $order->order_number }}</div>
                <div>Date: {{ $order->created_at->format('d M Y') }}</div>
                <div>Status: {{ Str::headline($order->status->value) }}</div>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Design Name</th>
                <th>Size (WxH)</th>
                <th>Rate</th>
                <th>Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <div>{{ $item->design_name ?? 'N/A' }}</div>
                        @if($item->designFiles->count() > 0)
                            @foreach($item->designFiles as $file)
                                <div style="font-size: 10px; color: #666; margin-top: 4px;">
                                    <a href="{{ url(Storage::url($file->file_path)) }}">{{ $file->original_name }}</a>
                                </div>
                            @endforeach
                        @else
                            <div style="font-size: 10px; color: #666; margin-top: 4px;">{{ __('No file uploaded') }}</div>
                        @endif
                    </td>
                    <td>{{ $item->width }}" x {{ $item->height }}" ({{ $item->square_inches }} sq.in)</td>
                    <td>{{ $currency_symbol }} {{ number_format($item->rate_per_inch, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">{{ $currency_symbol }} {{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right font-bold">Grand Total:</td>
                <td class="text-right font-bold">{{ $currency_symbol }} {{ number_format($order->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">Amount Paid:</td>
                <td class="text-right text-success">{{ $currency_symbol }} {{ number_format($order->paid_amount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right font-bold">Balance Due:</td>
                <td class="text-right font-bold {{ $order->pending_amount > 0 ? 'text-red' : '' }}">
                    {{ $currency_symbol }} {{ number_format($order->pending_amount, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    @if($order->payments->count() > 0)
        <div class="mt-10 font-bold">Payment History:</div>
        <table class="table" style="margin-top: 10px; font-size: 10px;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Notes</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                        <td>{{ Str::headline($payment->payment_method) }}</td>
                        <td>{{ $payment->notes }}</td>
                        <td class="text-right">{{ $currency_symbol }} {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($order->notes)
        <div class="mt-10">
            <span class="font-bold">Notes:</span><br>
            {{ $order->notes }}
        </div>
    @endif
@endsection
