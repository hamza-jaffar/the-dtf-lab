<?php

namespace App\Services;

use App\Models\Purchase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Get a paginated list of purchases with optional search and sorting.
     */
    public function getPaginated(
        string $search = '',
        string $sortBy = 'purchase_date',
        string $sortDirection = 'desc',
        ?string $startDate = null,
        ?string $endDate = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Purchase::query()
            ->when($startDate, fn($q) => $q->whereDate('purchase_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('purchase_date', '<=', $endDate))
            ->when($search, function ($query) use ($search) {
                $query->where('item_name', 'like', '%' . $search . '%')
                      ->orWhere('notes', 'like', '%' . $search . '%');
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);
    }

    /**
     * Get basic stats for purchases.
     */
    public function getStats(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Purchase::query()
            ->when($startDate, fn($q) => $q->whereDate('purchase_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('purchase_date', '<=', $endDate));

        return [
            'total_spent' => (clone $query)->sum('total_price'),
            'total_items' => (clone $query)->sum('quantity'),
        ];
    }

    /**
     * Find a purchase by ID.
     */
    public function find(int $id): Purchase
    {
        return Purchase::findOrFail($id);
    }

    /**
     * Create or update a purchase.
     */
    public function save(array $data, ?int $id = null): Purchase
    {
        $purchase = $id ? $this->find($id) : new Purchase();
        
        $purchase->item_name = $data['item_name'];
        $purchase->quantity = $data['quantity'] ?? 1;
        $purchase->unit_price = $data['unit_price'];
        // Auto-calculate total price
        $purchase->total_price = $purchase->quantity * $purchase->unit_price;
        $purchase->purchase_date = $data['purchase_date'];
        $purchase->notes = $data['notes'] ?? null;
        
        $purchase->save();
        
        return $purchase;
    }

    /**
     * Delete a purchase.
     */
    public function delete(int $id): bool
    {
        $purchase = $this->find($id);
        return $purchase->delete();
    }
}
