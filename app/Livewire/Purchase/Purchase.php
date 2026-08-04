<?php

namespace App\Livewire\Purchase;

use App\Services\PurchaseService;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Purchases')]
class Purchase extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'purchase_date';
    public $sortDirection = 'desc';

    public $purchaseId = null;

    public $item_name = '';
    public $quantity = 1;
    public $unit_price = '';
    public $purchase_date = '';
    public $notes = '';

    public $isEditing = false;

    public function mount()
    {
        $this->purchase_date = now()->format('Y-m-d');
    }

    public function rules()
    {
        return [
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }

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
        $this->reset(['purchaseId', 'item_name', 'quantity', 'unit_price', 'notes', 'isEditing']);
        $this->purchase_date = now()->format('Y-m-d');
        $this->quantity = 1;
    }

    public function create()
    {
        $this->resetForm();
        Flux::modal('purchase-modal')->show();
    }

    public function edit(int $id, PurchaseService $service)
    {
        $this->resetForm();

        $purchase = $service->find($id);

        $this->purchaseId = $purchase->id;
        $this->item_name = $purchase->item_name;
        $this->quantity = $purchase->quantity;
        $this->unit_price = $purchase->unit_price;
        $this->purchase_date = $purchase->purchase_date->format('Y-m-d');
        $this->notes = $purchase->notes;
        $this->isEditing = true;

        Flux::modal('purchase-modal')->show();
    }

    public function save(PurchaseService $service)
    {
        $data = $this->validate();

        $service->save($data, $this->purchaseId);

        Flux::modal('purchase-modal')->close();
        Flux::toast(variant: 'success', text: $this->purchaseId ? 'Purchase updated.' : 'Purchase created.');

        $this->resetForm();
    }

    public function delete(int $id, PurchaseService $service)
    {
        $service->delete($id);
        Flux::toast(variant: 'success', text: 'Purchase deleted.');
    }

    public function render(PurchaseService $service)
    {
        $stats = $service->getStats();
        
        return view('livewire.purchase.purchase', [
            'purchases' => $service->getPaginated(
                search: $this->search,
                sortBy: $this->sortBy,
                sortDirection: $this->sortDirection
            ),
            'total_spent' => $stats['total_spent'],
        ]);
    }
}
