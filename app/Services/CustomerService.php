<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    /**
     * Get a paginated list of customers with search and sorting.
     */
    public function getPaginated(string $search = '', string $sortBy = 'created_at', string $sortDirection = 'desc', int $perPage = 10): LengthAwarePaginator
    {
        return Customer::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);
    }

    /**
     * Create or update a customer.
     */
    public function save(array $data, ?int $id = null): Customer
    {
        return Customer::updateOrCreate(
            ['id' => $id],
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => !empty($data['email']) ? $data['email'] : null,
                'address' => !empty($data['address']) ? $data['address'] : null,
            ]
        );
    }

    /**
     * Delete a customer.
     */
    public function delete(int $id): bool
    {
        return Customer::findOrFail($id)->delete();
    }

    /**
     * Get a single customer by ID.
     */
    public function find(int $id): Customer
    {
        return Customer::findOrFail($id);
    }
}
