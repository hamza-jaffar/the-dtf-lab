<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DesignFile;
use App\Models\Payments;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Get filtered statistics for orders.
     */
    public function getStats(string $search = '', ?string $phoneFilter = null, ?string $startDate = null, ?string $endDate = null, ?string $status = null): array
    {
        $query = Order::query()
            ->when($phoneFilter, fn($q) => $q->whereHas(
                'customer', fn($q) => $q->where('phone', $phoneFilter)
            ))
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
            });

        return [
            'total_sales'     => (clone $query)->sum('total_amount'),
            'total_collected' => (clone $query)->sum('paid_amount'),
            'total_pending'   => (clone $query)->selectRaw('SUM(total_amount - COALESCE(paid_amount, 0)) as pending')->value('pending') ?? 0,
        ];
    }

    /**
     * Get a paginated list of orders with optional phone filter, search, and sorting.
     */
    public function getPaginated(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        ?string $phoneFilter = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $status = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Order::query()
            ->with(['customer'])
            ->when($phoneFilter, fn($q) => $q->whereHas(
                'customer', fn($q) => $q->where('phone', $phoneFilter)
            ))
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
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);
    }

    /**
     * Create a new order with items and optional design files.
     *
     * @param  array  $orderData
     * @param  array  $items     [ ['width', 'height', 'rate_per_inch', 'quantity', 'design_name', 'files'[]] ]
     */
    public function create(array $orderData, array $items): Order
    {
        return DB::transaction(function () use ($orderData, $items) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id'  => $orderData['customer_id'],
                'status'       => $orderData['status'] ?? OrderStatus::Pending->value,
                'paid_amount'  => $orderData['paid_amount'] ?? 0,
                'notes'        => $orderData['notes'] ?? null,
                'total_amount' => 0,
            ]);

            $this->syncItems($order, $items);

            // Create initial payment record if paid_amount > 0
            if (($orderData['paid_amount'] ?? 0) > 0) {
                Payments::create([
                    'order_id'       => $order->id,
                    'amount'         => $orderData['paid_amount'],
                    'payment_method' => 'cash',
                    'payment_date'   => now(),
                    'notes'          => 'Initial payment upon order creation.',
                ]);
            }

            return $order->fresh(['items.designFiles']);
        });
    }

    /**
     * Update an existing order.
     */
    public function update(Order $order, array $orderData, array $items): Order
    {
        return DB::transaction(function () use ($order, $orderData, $items) {
            $order->update([
                'customer_id'    => $orderData['customer_id'],
                'status'         => $orderData['status'],
                'paid_amount'    => $orderData['paid_amount'] ?? 0,
                'notes'          => $orderData['notes'] ?? null,
                'price_override' => $orderData['price_override'] ?? null,
            ]);

            // Remove old items (files are cascade-deleted by DB)
            foreach ($order->items as $item) {
                foreach ($item->designFiles as $file) {
                    Storage::disk('public')->delete($file->file_path);
                }
            }
            $order->items()->delete();

            $this->syncItems($order, $items);

            return $order->fresh(['items.designFiles']);
        });
    }

    /**
     * Update the manual price override on an order (client bargain adjustment).
     */
    public function updatePriceOverride(Order $order, ?float $override): Order
    {
        $order->update(['price_override' => $override]);
        return $order->fresh();
    }

    /**
     * Delete an order and its associated storage files.
     */
public function delete(Order $order): bool
{
    return DB::transaction(function () use ($order) {

        // Delete design files from storage
        foreach ($order->items as $item) {
            foreach ($item->designFiles as $file) {
                Storage::disk('public')->delete($file->file_path);
            }

            // Optional: delete related design files records
            $item->designFiles()->delete();
        }

        // Delete payments related to this order
        $order->payments()->delete();

        // Optional: delete order items
        $order->items()->delete();

        // Finally delete the order
        return $order->delete();
    });
}

    /**
     * Find an order by ID with items and files eager loaded.
     */
    public function find(int $id): Order
    {
        return Order::with(['customer', 'items.designFiles'])->findOrFail($id);
    }

    /**
     * Build order items, compute totals, and store design files.
     * Supports two pricing types:
     *   per_sqin  — width × height × rate × qty  (default)
     *   per_piece — rate × qty  (flat price per piece, dimensions ignored in calc)
     */
    private function syncItems(Order $order, array $items): void
    {
        $total = 0;

        foreach ($items as $itemData) {
            $pricingType  = $itemData['pricing_type'] ?? 'per_sqin';
            $width        = (float) ($itemData['width']  ?? 0);
            $height       = (float) ($itemData['height'] ?? 0);
            $quantity     = (int)   ($itemData['quantity'] ?? 1);
            $rate         = (float) $itemData['rate_per_inch'];

            if ($pricingType === 'per_piece') {
                // Flat rate per piece — dimensions irrelevant to price
                $squareInches = round($width * $height, 2);
                $totalPrice   = round($rate * $quantity, 2);
            } else {
                // Default: rate per square inch
                $squareInches = round($width * $height, 2);
                $totalPrice   = round($squareInches * $rate * $quantity, 2);
            }

            $total += $totalPrice;

            $item = OrderItem::create([
                'order_id'      => $order->id,
                'width'         => $width,
                'height'        => $height,
                'square_inches' => $squareInches,
                'rate_per_inch' => $rate,
                'total_price'   => $totalPrice,
                'design_name'   => $itemData['design_name'] ?? null,
                'quantity'      => $quantity,
                'pricing_type'  => $pricingType,
            ]);

            if (!empty($itemData['files'])) {
                foreach ($itemData['files'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $path = $file->store('design-files', 'public');
                        DesignFile::create([
                            'order_item_id' => $item->id,
                            'file_path'     => $path,
                            'original_name' => $file->getClientOriginalName(),
                        ]);
                    }
                }
            }
        }

        // Reset price_override when items are re-synced (order was edited from scratch)
        $order->update(['total_amount' => round($total, 2), 'price_override' => null]);
    }

    /**
     * Generate a unique sequential order number like ORD-00042.
     */
    private function generateOrderNumber(): string
    {
        $last = Order::max('id') ?? 0;
        return 'ORD-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}
