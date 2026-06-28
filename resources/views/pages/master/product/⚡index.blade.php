<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $readyToLoadCategories = false;

    public $category_id = '';
    public $name = '';
    public $description = '';
    public ?Product $sProduct = null;

    #[Computed]
    public function products()
    {
        return Product::query()
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->with('category')
                        ->latest()
                        ->paginate(10);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function loadCategories()
    {
        $this->readyToLoadCategories = true;
    }

    #[Computed]
    public function categories()
    {
        if (!$this->readyToLoadCategories) {
            return collect();
        }

        return Category::all();
    }

    public function save()
    {
        $this->validate([
            'category_id' => 'required',
            'name' => 'required|max:30',
            'description' => 'required|max:100',
        ]);

        if ($this->sProduct) {
            $this->sProduct->update([
                'category_id' => $this->category_id,
                'name' => $this->name,
                'description' => $this->description,
            ]);
        } else {
            Product::create([
                'category_id' => $this->category_id,
                'name' => $this->name,
                'description' => $this->description,
            ]);
        }

        $this->dispatch('saved');
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->resetErrorBag();
        $this->reset(['category_id', 'name', 'description', 'sProduct']);
    }

    public function edit(Product $product)
    {
        $this->resetErrorBag();

        $this->sProduct = $product;
    }

    public function destroy(Product $product)
    {
        $product->delete();

        $this->resetForm();
        $this->dispatch('deleted');
    }
};
?>

<div x-data="productComponent" 
    @saved.window="open = false; triggerAlert(isEdit ? 'Updated Successfully!' : 'Added Successfully!', 'alert-success');" 
    @open-modal-edit.window="fillData($event.detail); $wire.loadCategories();"
    @deleted.window="openDelete = false; triggerAlert('Deleted Successfully', 'alert-error');"
    class="p-4 md:p-8 max-w-7xl mx-auto space-y-6">
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm">
        <button class="btn btn-primary shadow-sm" @click="open = !open; isEdit = false; $wire.loadCategories()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add Data
        </button>

        <div class="form-control w-full sm:max-w-xs">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search categories..."
                class="input input-bordered w-full" />
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-base-200 bg-base-100 shadow-sm relative">
        {{-- Loading Overlay --}}
        <div wire:loading wire:target="search" class="absolute inset-0 bg-base-100/50 z-10">
            <div class="flex items-center justify-center h-full">
                <div class="flex flex-col items-center gap-2">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <span class="text-xs font-medium text-base-content/70">Memuat data...</span>
                </div>
            </div>
        </div>

        <div wire:loading.class="opacity-50" wire:target="search">
            <table class="table table-zebra w-full">
                <thead>
                    <tr class="bg-base-200/50">
                        <th class="w-20">No</th>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="w-44 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->products as $data)
                    <tr wire:key="product-row-{{ $data->id }}">
                        <td>
                            {{ ($this->products->currentPage() - 1) * $this->products->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $data->category->name }}</td>
                        <td>{{ $data->name }}</td>
                        <td>{{ $data->description }}</td>
                        <td class="text-center">
                            <div class="flex justify-center gap-2">
                                <button class="btn btn-sm btn-ghost text-primary" @click="$dispatch('open-modal-edit', {{ $data->toJson() }}); $wire.edit({{ $data->id }})">Edit</button>
                                <button class="btn btn-sm btn-ghost text-error" @click="openDelete = true; deleteId = {{ $data->id }}">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-base-content/50 italic">
                            No data available.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-base-200 flex items-center justify-between gap-4 bg-base-50">
            <div class="text-sm text-base-content/70">
                Showing {{ $this->products->firstItem() ?? 0 }} to {{ $this->products->lastItem() ?? 0 }} of
                {{ $this->products->total() }} entries
            </div>
            <div>
                {{ $this->products->links(data: ['scrollTo' => false]) }}
            </div>
        </div>
    </div>

    <div class="modal" :class="{ 'modal-open' : open }" x-show="open" x-transition role="dialog" x-effect="if (!open) {$wire.resetForm(); resetRemainder()}">
        <div class="modal-box max-w-md">
            <h3 class="text-lg font-bold border-b pb-3" x-text="isEdit ? 'Edit Product' : 'Add New Product'"></h3>

            <div class="py-4">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-semibold">Category Name</span>
                    </label>
                    <select class="input input-bordered w-full @error('category_id') input-error @enderror" x-model="category_id">
                        <option value="">--- Pilih Category Name ---</option>
                        @foreach ($this->categories as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <span class="text-error text-xs mt-1">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-semibold">Name</span>
                        <span class="text-xs text-base-content/50" :class="{'text-error': name.length > 20}">
                           Sisa <span x-text="remainderMaxName"></span> Karakter
                        </span>
                    </label>
                    <input type="text" class="input input-bordered w-full @error('name') input-error @enderror" x-model="name" :maxlength="maxName" @keyup="nameKeyup">
                    @error('name')
                        <span class="text-error text-xs mt-1">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-semibold">Description</span>
                        <span class="text-xs text-base-content/50" :class="{'text-error': description.length > 90}">
                           Sisa <span x-text="remainderMaxDescription"></span> Karakter
                        </span>
                    </label>
                    <textarea x-model="description" cols="30" rows="10" class="textarea textarea-bordered w-full @error('description') input-error @enderror" :maxlength="maxDescription" @keyup="descriptionKeyup"></textarea>
                    @error('description')
                        <span class="text-error text-xs mt-1">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            </div>

            <div class="modal-action border-t pt-3">
                <button class="btn btn-ghost" @click="open = !open;">Cancel</button>
                <button wire:click="save" class="btn btn-primary px-6" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                    <span>Save</span>
                </button>
            </div>
        </div>
    </div>

    <div class="modal" :class="{ 'modal-open': openDelete }" x-show="openDelete" x-transition role="dialog">
        <div class="modal-box max-w-sm text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-error/10 text-error mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
            </div>
            <h3 class="text-lg font-bold text-error">Confirm Deletion</h3>
            <p class="py-2 text-sm text-base-content/70">Are you sure you want to delete this category? This action
                cannot be undone.</p>

            <div class="modal-action flex justify-center gap-2 border-t pt-3">
                <button @click="openDelete = false" class="btn btn-ghost">Cancel</button>
                <button @click="$wire.destroy(deleteId)" class="btn btn-error px-6" wire:loading.attr="disabled">
                    <span wire:loading wire:target="destroy" class="loading loading-spinner loading-sm"></span>
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    {{-- TOAST NOTIFICATION --}}
    <div class="toast toast-end toast-top z-[9999]" x-show="showAlert" x-transition>
        <div class="alert shadow-lg" :class="alertType">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-text="alertMessage" class="text-sm font-medium"></span>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('productComponent', () => ({
        open: false,
        isEdit: false,

        name: $wire.entangle('name'),
        maxName: 30,
        remainderMaxName: 30,

        description: $wire.entangle('description'),
        maxDescription: 100,
        remainderMaxDescription: 100,

        category_id: $wire.entangle('category_id'),

        openDelete: false,
        deleteId: null,

        showAlert: false,
        alertType: '',
        alertMessage: 'alert-success', 

        triggerAlert(message, type = 'alert-message') {
            this.alertMessage = message;
            this.alertType = type;
            this.showAlert = true;
            setTimeout(() => { this.showAlert = false }, 3000);
        },

        fillData(product) {
            this.isEdit = true;
            this.open = true;
            this.category_id = product.category_id;
            this.name = product.name;
            this.description = product.description;
            
            this.nameKeyup();
            this.descriptionKeyup();
        },

        resetRemainder() {
            this.remainderMaxName = this.maxName;
            this.remainderMaxDescription = this.maxDescription;
        },

        descriptionKeyup() {
            this.remainderMaxDescription = this.maxDescription - (this.description ? this.description.length : 0);
        },

        nameKeyup() {
            this.remainderMaxName = this.maxName - (this.name ? this.name.length : 0);
        },
    }));
</script>
@endscript