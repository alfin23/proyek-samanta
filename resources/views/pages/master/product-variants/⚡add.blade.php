<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

new class extends Component
{
    #[Validate('required', message: 'Silahkan pilih produk terlebih dahulu.')]
    public $product_id;

    public $variants = [
        ['color' => '', 'size' => '', 'stock' => '']
    ];

    public $validationErrors = [];

    #[Computed]
    public function products()
    {
        return Product::pluck('name', 'id')->toArray();
    }

    public function save()
    {
        try {
            $this->reset('validationErrors');
            $this->validate([
                'product_id' => 'required',
                'variants' => 'required|array|min:1',
                'variants.*.color' => 'required|string|max:100',
                'variants.*.size' => 'required|string|max:100',
                'variants.*.stock' => 'required|integer|min:0',
            ], [
                'product_id.required' => 'Silahkan pilih produk terlebih dahulu.',
                'variants.required' => 'Silahkan tambahkan varian terlebih dahulu.',
                'variants.min' => 'Minimal harus ada 1 varian.',
                'variants.*.color.required' => 'Warna wajib diisi.',
                'variants.*.size.required' => 'Ukuran wajib diisi.',
                'variants.*.stock.required' => 'Stok wajib diisi.',
                'variants.*.stock.integer' => 'Stok harus berupa angka.',
                'variants.*.stock.min' => 'Stok minimal 0.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->validationErrors = $e->validator->errors()->toArray();
            throw $e;
        }

        DB::transaction(function () {
            foreach ($this->variants as $variant) {
                ProductVariant::create([
                    'product_id' => $this->product_id,
                    'color' => $variant['color'],
                    'size' => $variant['size'],
                    'stock' => $variant['stock'],
                ]);
            }
        });

        $this->dispatch('saved');
        $this->reset(['product_id', 'variants', 'validationErrors']);
        $this->variants = [['color' => '', 'size' => '', 'stock' => '']];
    }
};
?>

<div class="p-4 md:p-8 max-w-7xl mx-auto space-y-6" x-data="variantForm" @saved.window="triggerAlert('Product variants added successfully!', 'alert-success')">

    <div class="rounded-xl border border-base-200 bg-base-100 shadow-sm p-6 md:p-8">
        <h2 class="text-xl font-bold border-b pb-4 mb-6">Add New Product Variant</h2>

        <form class="space-y-6" wire:submit="save">
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-semibold">Product Name</span>
                </label>
                <select class="select select-bordered w-full @error('product_id') input-error @enderror" wire:model="product_id">
                    <option value="">Select Product</option>
                    @foreach ($this->products as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                @error('product_id') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <button type="button" @click="addVariant" class="btn btn-primary">
                Add Product Variant
            </button>

            <div class="flex flex-wrap gap-4">
                <template x-for="(variant, index) in variants" :key="index">
                    <div x-transition class="border border-base-200 rounded-xl p-4 space-y-4 w-full md:w-[calc(33.333%-0.89rem)] relative">
                        
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Color</span>
                            </label>
                            <input
                                type="text"
                                placeholder="Type color here..."
                                class="input input-bordered w-full"
                                :class="getError(index, 'color') ? 'input-error' : ''"
                                x-model="variant.color"
                            />
                            <span class="text-error text-xs mt-1" x-text="getError(index, 'color')"></span>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Size</span>
                            </label>
                            <input
                                type="text"
                                placeholder="Type size here..."
                                class="input input-bordered w-full"
                                :class="getError(index, 'size') ? 'input-error' : ''"
                                x-model="variant.size"
                            />
                            <span class="text-error text-xs mt-1" x-text="getError(index, 'size')"></span>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Stock</span>
                            </label>
                            <input
                                type="number"
                                min="0"
                                placeholder="Type stock here..."
                                class="input input-bordered w-full"
                                :class="getError(index, 'stock') ? 'input-error' : ''"
                                x-model="variant.stock"
                            />
                            <span class="text-error text-xs mt-1" x-text="getError(index, 'stock')"></span>
                        </div>

                        <template x-if="variants.length > 1">
                            <button type="button" @click="removeVariant(index)" class="btn btn-error text-white btn-sm mt-2">
                                Remove
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-2 border-t pt-4">
                <a href="{{ route('pv.index') }}" wire:navigate class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary px-6">Save</button>
            </div>
        </form>
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
    Alpine.data('variantForm', () => ({
        variants: $wire.entangle('variants'),
        errors: $wire.entangle('validationErrors'),
        showAlert: false,
        alertMessage: '',
        alertType: 'alert-success',

        addVariant() {
            this.variants = [...this.variants, { color: '', size: '', stock: '' }];
        },

        removeVariant(index) {
            if (this.variants.length > 1) {
                this.variants = this.variants.filter((_, i) => i !== index);
            }
        },

        getError(index, field) {
            let key = `variants.${index}.${field}`;
            return this.errors && this.errors[key] ? this.errors[key][0] : null;
        },

        triggerAlert(message, type = 'alert-success') {
            this.alertMessage = message;
            this.alertType = type;
            this.showAlert = true;
            setTimeout(() => { this.showAlert = false }, 3000);
        }
    }));
</script>
@endscript