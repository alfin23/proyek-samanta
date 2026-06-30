<?php

use App\Models\ProductVariant;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[Computed]
    public function productVariants()
    {
        return ProductVariant::query()
                        ->with('product')
                        ->where(function ($query) {
                            $query->whereHas('product', function($subQuery) {
                                        $subQuery->where('name', 'LIKE', '%' . $this->search . '%');
                                    });
                        })
                        ->groupBy('product_id')
                        ->latest()
                        ->paginate(10);
    }
};
?>

<div class="p-4 md:p-8 max-w-7xl mx-auto space-y-6">
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
                    @forelse ($this->productVariants as $item)
                    <tr wire:key="pv-row-{{ $item->id }}">
                        <td>
                            {{ ($this->productVariants->currentPage() - 1) * $this->productVariants->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-center">
                            <div class="flex justify-center gap-2">
                                <a class="btn btn-sm btn-ghost text-primary" href="{{ route('pv.edit', $item->id) }}"
                                    wire:navigate>Edit</a>
                                <button class="btn btn-sm btn-ghost text-error">Delete</button>
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
                    Showing {{ $this->productVariants->firstItem() ?? 0 }} to {{ $this->productVariants->lastItem() ?? 0 }} of
                    {{ $this->productVariants->total() }} entries
                </div>
                @if ($this->productVariants->hasPages())
                <div>
                    {{ $this->productVariants->links(data: ['scrollTo' => false]) }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
