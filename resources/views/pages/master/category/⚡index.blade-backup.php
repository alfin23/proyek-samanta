<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

new class extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?int $categoryId = null; // Menyimpan ID saat mode edit
    public bool $showDeleteModal = false; // Untuk modal konfirmasi delete
    public ?int $categoryIdToDelete = null; // ID kategori yang akan dihapus
    public string $name = '';
    public string $search = ''; // State untuk input pencarian

    public function openModal()
    {
        $this->resetErrorBag();
        $this->reset(['name', 'categoryId']); // Reset agar bersih untuk mode "Add"
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $this->resetErrorBag();
        $category = Category::findOrFail($id);
        
        $this->categoryId = $category->id;
        $this->name = $category->name;
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function confirmDelete(int $id)
    {
        $this->categoryIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->categoryIdToDelete = null;
    }

    public function deleteCategory()
    {
        Category::findOrFail($this->categoryIdToDelete)->delete();
        $this->closeDeleteModal();
        // Livewire akan otomatis me-refresh tabel karena `datas` adalah computed property
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
            'name' => 'required|min:3|unique:categories,name,' . $this->categoryId,
        ]);

        if ($this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $category->update(['name' => $this->name]);
        } else {
            Category::create(['name' => $this->name]);
        }

        $this->closeModal();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
};
?>

<div class="p-8 max-w-7xl mx-auto">
    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 shadow-md">
        <div class="flex flex-col md:flex-row items-center justify-between p-4 gap-4">
            <button wire:click="openModal" class="btn btn-primary w-full md:w-auto">Add Data</button>
            
            <div class="form-control w-full max-w-xs">
                <div class="input-group">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search categories..." class="input input-bordered w-full" />
                </div>
            </div>
        </div>

        <table class="table">
            <!-- head -->
            <thead>
                <tr>
                    <th>No</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->datas as $data)
                <tr>
                    <th>{{ ($this->datas->currentPage() - 1) * $this->datas->perPage() + $loop->iteration }}</th>
                    <td>{{ $data->name }}</td>
                    <td>
                        <button wire:click="edit({{ $data->id }})" class="btn btn-sm btn-primary">Edit</button>
                        <button wire:click="confirmDelete({{ $data->id }})" class="btn btn-sm btn-error text-white">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">
                        No data available.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if ($this->datas->total() > 0 && $this->datas->hasPages())
        <div class="card-footer flex items-center justify-end text-white mt-4">
            {{ $this->datas->links(data: ['scrollTo' => false]) }}
        </div>
        @else
        <div class="card-footer flex items-center justify-end text-white mt-4">
            No more data to display.
        </div>
        @endif
    </div>

    <!-- Modal Add Category -->
    <div class="modal {{ $showModal ? 'modal-open' : '' }}" role="dialog">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                {{ $categoryId ? 'Edit Category' : 'Add New Category' }}
            </h3>

            <div class="py-4">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">Category Name</span>
                    </label>
                    <input type="text" wire:model="name" placeholder="Type here..."
                        class="input input-bordered w-full @error('name') input-error @enderror" />
                    @error('name')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="modal-action">
                <button wire:click="closeModal" class="btn">Cancel</button>
                <button wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading class="loading loading-spinner"></span>
                    {{ $categoryId ? 'Update' : 'Save' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Delete Confirmation -->
    <div class="modal {{ $showDeleteModal ? 'modal-open' : '' }}" role="dialog">
        <div class="modal-box">
            <h3 class="text-lg font-bold text-error">Confirm Deletion</h3>
            
            <div class="py-4">
                <p>Are you sure you want to delete this category?</p>
                <p class="font-bold text-warning">This action cannot be undone.</p>
            </div>

            <div class="modal-action">
                <button wire:click="closeDeleteModal" class="btn">Cancel</button>
                <button wire:click="deleteCategory" class="btn btn-error text-white" wire:loading.attr="disabled">
                    <span wire:loading class="loading loading-spinner"></span>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
