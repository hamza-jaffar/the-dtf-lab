<?php

namespace App\Livewire\Customer;

use App\Services\CustomerService;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Customers')]
class Customer extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    public $customerId = null;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|string|max:20')]
    public $phone = '';

    #[Validate('nullable|email|max:255')]
    public $email = '';

    #[Validate('nullable|string')]
    public $address = '';

    public $isEditing = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->reset(['customerId', 'name', 'phone', 'email', 'address', 'isEditing']);
    }

    public function create()
    {
        $this->resetForm();
    }

    public function edit(int $id, CustomerService $service)
    {
        $this->resetForm();
        
        $customer = $service->find($id);
        
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->address = $customer->address;
        $this->isEditing = true;
        
        Flux::modal('customer-modal')->show();
    }

    public function save(CustomerService $service)
    {
        $data = $this->validate();

        $service->save($data, $this->customerId);

        Flux::modal('customer-modal')->close();
        Flux::toast(variant: 'success', text: $this->customerId ? 'Customer updated.' : 'Customer created.');
        
        $this->resetForm();
    }

    public function delete(int $id, CustomerService $service)
    {
        $service->delete($id);
        
        Flux::toast(variant: 'success', text: 'Customer deleted.');
    }

    public function render(CustomerService $service)
    {
        return view('livewire.customer.customer', [
            'customers' => $service->getPaginated(
                search: $this->search,
                sortBy: $this->sortBy,
                sortDirection: $this->sortDirection
            ),
        ]);
    }
}
