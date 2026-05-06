<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DesignFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Get a paginated list of orders with optional phone filter, search, and sorting.
     */
    public function getPaginated(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDirection = 'desc',
        ?string $phoneFilter = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Order::query()
            ->with(['customer'])
            ->when($phoneFilter, fn($q) => $q->whereHas(
                'customer', fn($q) => $q->where('phone', $phoneFilter)
            ))
            ->when($search, function ($query) use ($search) {
                $query->where('order_number', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', fn($q) => $q
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                    );
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
                'customer_id' => $orderData['customer_id'],
                'status'      => $orderData['status'],
                'paid_amount' => $orderData['paid_amount'] ?? 0,
                'notes'       => $orderData['notes'] ?? null,
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
     * Delete an order and its associated storage files.
     */
    public function delete(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                foreach ($item->designFiles as $file) {
                    Storage::disk('public')->delete($file->file_path);
                }
            }
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
     */
    private function syncItems(Order $order, array $items): void
    {
        $total = 0;

        foreach ($items as $itemData) {
            $width        = (float) $itemData['width'];
            $height       = (float) $itemData['height'];
            $quantity     = (int)   ($itemData['quantity'] ?? 1);
            $ratePerInch  = (float) $itemData['rate_per_inch'];
            $squareInches = round($width * $height, 2);
            $totalPrice   = round($squareInches * $ratePerInch * $quantity, 2);
            $total       += $totalPrice;

            $item = OrderItem::create([
                'order_id'      => $order->id,
                'width'         => $width,
                'height'        => $height,
                'square_inches' => $squareInches,
                'rate_per_inch' => $ratePerInch,
                'total_price'   => $totalPrice,
                'design_name'   => $itemData['design_name'] ?? null,
                'quantity'      => $quantity,
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

        $order->update(['total_amount' => round($total, 2)]);
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
