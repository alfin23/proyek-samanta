<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Category;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $name = '';
    public string $search = ''; // State untuk input pencarian
    public ?Category $selectedCategory = null;

    public function resetForm()
    {
        $this->resetErrorBag();
        $this->reset(['name', 'selectedCategory']);
    }

    #[Computed]
    public function datas()
    {
        return Category::query()
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->latest()
                        ->paginate(10);
    }

    public function save()
    {
        // Validasi dinamis: Abaikan ID saat ini jika sedang mengedit (unique rule)
        $this->validate([
            'name' => 'required|min:3|unique:categories,name,' . ($this->selectedCategory?->id ?? 'NULL'),
        ]);

        if ($this->selectedCategory) {
            $this->selectedCategory->update(['name' => $this->name]);
        } else {
            Category::create(['name' => $this->name]);
        }

        $this->resetForm();
        // Kirim sinyal ke browser bahwa data sukses disimpan
        $this->dispatch('saved');
    }

    public function edit(Category $category)
    {
        $this->resetErrorBag();
        $this->selectedCategory = $category;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function destroy(Category $category)
    {
        $category->delete();

        $this->resetForm();
        $this->dispatch('deleted');
    }
};
?>

<div x-data="categoryComponent" @saved.window="open = false; triggerAlert(isEdit ? 'Category updated successfully!' : 'Category created successfully!', 'alert-success')" @deleted.window="openDelete = false; triggerAlert('Category deleted successfully!', 'alert-error')"  class="p-4 md:p-8 max-w-7xl mx-auto space-y-6">
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm">
        <button @click="open = !open; isEdit = false; $wire.resetForm()" class="btn btn-primary shadow-sm">
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

    <div class="overflow-x-auto rounded-xl border border-base-200 bg-base-100 shadow-sm">
        <table class="table table-zebra w-full">
            <thead>
                <tr class="bg-base-200/50">
                    <th class="w-20">No</th>
                    <th>Name</th>
                    <th class="w-44 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->datas as $data)
                <tr wire:key="category-row-{{ $data->id }}">
                    <th>{{ ($this->datas->currentPage() - 1) * $this->datas->perPage() + $loop->iteration }}</th>
                    <td class="font-medium">{{ $data->name }}</td>
                    <td class="text-center">
                        <div class="flex justify-center gap-2">
                            <button @click="open = true; isEdit = true; name='{{ $data->name }}'; $wire.edit({{ $data->id }})" class="btn btn-sm btn-ghost text-primary">Edit</button>
                            <button @click="openDelete = true; deleteId= {{ $data->id }}" class="btn btn-sm btn-ghost text-error">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-8 text-base-content/50 italic">
                        No data available.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-base-200 flex items-center justify-between gap-4 bg-base-50">
            <div class="text-sm text-base-content/70">
                Showing {{ $this->datas->firstItem() ?? 0 }} to {{ $this->datas->lastItem() ?? 0 }} of
                {{ $this->datas->total() }} entries
            </div>
            @if ($this->datas->hasPages())
            <div>
                {{ $this->datas->links(data: ['scrollTo' => false]) }}
            </div>
            @endif
        </div>
    </div>

    {{-- MODAL ADD --}}
    <div class="modal" :class="{ 'modal-open': open }" x-show="open" x-transition role="dialog">
        <div class="modal-box max-w-md">
            <h3 class="text-lg font-bold border-b pb-3" x-text="isEdit ? 'Edit Category' : 'Add New Category'"></h3>

            <div class="py-4">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-semibold">Category Name</span>
                    </label>
                    {{-- Gunakan x-model agar sinkron instan dengan Alpine --}}
                    <input
                        type="text"
                        x-model="name"
                        placeholder="Type category name here..."
                        class="input input-bordered w-full @error('name') input-error @enderror"
                    />
                    
                    @error('name')
                        <span class="text-error text-xs mt-1">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            </div>

            <div class="modal-action border-t pt-3">
                <button @click="open = false; isEdit = false; isEdit ? $wire.resetErrorBag() : $wire.resetForm()" class="btn btn-ghost">Cancel</button>
                <button wire:click="save" class="btn btn-primary px-6" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                    <span x-text="isEdit ? 'Update' : 'Save'"></span>
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
    // Langsung daftarkan ke Alpine tanpa addEventListener('alpine:init')
    Alpine.data('categoryComponent', () => ({
        open: false, 
        isEdit: false, 
        openDelete: false, 
        deleteId: null, 
        
        // Gunakan $wire langsung (bukan Alpine.$wire)
        name: $wire.entangle('name'), 
        
        showAlert: false,
        alertMessage: '',
        alertType: 'alert-success',

        triggerAlert(message, type = 'alert-success') {
            this.alertMessage = message;
            this.alertType = type;
            this.showAlert = true;
            setTimeout(() => { this.showAlert = false }, 3000);
        },
    }));
</script>
@endscript