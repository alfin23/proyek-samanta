<?php

use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function datas()
    {
        return ProductVariant::query()
                        ->with('product')
                        ->whereHas('product', function($query) {
                            $query->where('name', 'LIKE', '%' . $this->search . '%');
                        })
                        ->groupBy('product_id')
                        ->latest()
                        ->paginate(10);
    }

    public function destroy($id)
    {
        $productVariant = ProductVariant::where('product_id', $id)->exists();

        if (!$productVariant) {
            $this->dispatch('error', 'Product variant not found');
            return;
        }

        ProductVariant::where('product_id', $id)->delete();

        $this->dispatch('deleted');
    }
};
?>

<div x-data="pageComponent" @deleted.window="openDelete = false; triggerAlert('Product variant deleted successfully!', 'alert-error')" class="p-4 md:p-8 max-w-7xl mx-auto space-y-6">
    {{-- No surplus words or unnecessary actions. - Marcus Aurelius --}}

    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm">
        <a class="btn btn-primary shadow-sm" href="{{ route('pv.add') }}" wire:navigate>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add Data
        </a>

        <div class="form-control w-full sm:max-w-xs">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search product variants..."
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
                        <th>Product</th>
                        <th class="w-44 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->datas as $item)
                    <tr wire:key="pv-row-{{ $item->id }}">
                        <td>
                            {{ ($this->datas->currentPage() - 1) * $this->datas->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-center">
                            <div class="flex justify-center gap-2">
                                <a class="btn btn-sm btn-ghost text-primary" href="{{ route('pv.edit', $item->product_id) }}"
                                    wire:navigate>Edit</a>
                                <a class="btn btn-sm btn-ghost text-error" @click="openDelete = true; deleteId = {{ $item->product_id }}">Delete</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr colspan="6" class="text-center py-8 text-base-content/50 italic">
                        No data available.
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
    Alpine.data('pageComponent', () => ({
        openDelete: false,
        deleteId: null,
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