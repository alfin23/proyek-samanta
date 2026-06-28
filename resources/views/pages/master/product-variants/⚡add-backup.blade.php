<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

new class extends Component
{
    #[Validate('required', message: 'Silahkan pilih produk terlebih dahulu.')]
    public $product_id;

    public array $variantErrors = [];

    #[Computed]
    public function products()
    {
        return Product::pluck('name', 'id')->toArray();
    }

    public function save($variants)
    {
        // 1. Reset variant errors setiap kali save dipanggil
        $this->variantErrors = [];

        // 2. Validasi produk_id yang ada di properti livewire
        $this->validateOnly('product_id');

        // 3. Validasi array $variants dari Alpine menggunakan Validator Facade
        $validator = Validator::make(
            ['variants' => $variants],
            [
                'variants'        => 'required|array|min:1',
                'variants.*.color' => 'required|string',
                'variants.*.size'  => 'required|string',
                'variants.*.stock' => 'required|integer|min:0',
            ],
            [
                'variants.*.color.required' => 'Color wajib diisi.',
                'variants.*.size.required'  => 'Size wajib diisi.',
                'variants.*.stock.required' => 'Stock wajib diisi.',
                'variants.*.stock.integer'  => 'Stock harus berupa angka.',
                'variants.*.stock.min'      => 'Stock tidak boleh negatif.',
            ]
        );

        if ($validator->fails()) {
            $this->variantErrors = $validator->errors()->toArray();

            return;
        }

        $validatedData = $validator->validated();

        // 4. Simpan ke database jika validasi lolos
        DB::transaction(function () use ($validatedData) {
            foreach ($validatedData['variants'] as $variant) {
                ProductVariant::create([
                    'product_id' => $this->product_id,
                    'color'      => $variant['color'],
                    'size'       => $variant['size'],
                    'stock'      => $variant['stock'],
                ]);
            }
        });

        $this->dispatch('saved');
    }
};
?>

<div x-data="addPVComponent" class="p-4 md:p-8 max-w-7xl mx-auto space-y-6">
    {{-- When there is no desire, all things are at peace. - Laozi --}}

    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-base-100 p-4 rounded-xl border border-base-200 shadow-sm">
        <a class="btn btn-error shadow-sm text-white" href="{{ route('supplier.index') }}" wire:navigate>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back
        </a>
    </div>

    <div class="rounded-xl border border-base-200 bg-base-100 shadow-sm relative p-6 md:p-8">
        <h2 class="text-xl font-bold border-b pb-4 mb-6">Add New Product Variant</h2>

        <form class="space-y-6" @submit.prevent="$wire.save(variants)">
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

                @error('product_id')
                    <span class="text-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="button" @click="addVariant()" class="btn btn-primary">Add Product Variant</button>

            <div class="flex flex-wrap gap-4">
                <template x-for="(variant, index) in variants" :key="index">
                    <div
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        :style="variants.length === 1
                            ? 'width: 100%; transition: width 0.5s ease;'
                            : 'width: calc(33.333% - 0.89rem); transition: width 0.5s ease;'"
                        class="border border-base-200 rounded-xl p-4 space-y-4"
                    >
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Color</span>
                            </label>
                            <input
                                type="text"
                                placeholder="Type color here..."
                                :class="$wire.variantErrors['variants.' + index + '.color'] ? 'input input-bordered w-full input-error' : 'input input-bordered w-full'"
                                x-model="variant.color"
                            />
                            <template x-if="$wire.variantErrors['variants.' + index + '.color']">
                                <span class="text-error text-sm mt-1" x-text="$wire.variantErrors['variants.' + index + '.color'][0]"></span>
                            </template>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Size</span>
                            </label>
                            <input
                                type="text"
                                placeholder="Type size here..."
                                :class="$wire.variantErrors['variants.' + index + '.size'] ? 'input input-bordered w-full input-error' : 'input input-bordered w-full'"
                                x-model="variant.size"
                            />
                            <template x-if="$wire.variantErrors['variants.' + index + '.size']">
                                <span class="text-error text-sm mt-1" x-text="$wire.variantErrors['variants.' + index + '.size'][0]"></span>
                            </template>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold">Stock</span>
                            </label>
                            <input
                                type="number"
                                min="0"
                                placeholder="Type stock here..."
                                :class="$wire.variantErrors['variants.' + index + '.stock'] ? 'input input-bordered w-full input-error' : 'input input-bordered w-full'"
                                x-model="variant.stock"
                            />
                            <template x-if="$wire.variantErrors['variants.' + index + '.stock']">
                                <span class="text-error text-sm mt-1" x-text="$wire.variantErrors['variants.' + index + '.stock'][0]"></span>
                            </template>
                        </div>

                        <button type="button" @click="removeVariant(index)" class="btn btn-error text-white">Remove</button>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-2 border-t pt-4">
                <a href="{{ route('pv.index') }}" wire:navigate class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary px-6">Save</button>
            </div>
        </form>
    </div>
</div>

@script
<script>
    Alpine.data('addPVComponent', () => ({
        variants: [{
            color: '',
            size: '',
            stock: '',
        }],

        addVariant() {
            this.variants.push({
                color: '',
                size: '',
                stock: '',
            })
        },

        removeVariant(index) {
            if (this.variants.length > 1) {
                this.variants.splice(index, 1)
            } else {
                alert('Minimal harus ada 1 varian!');
            }
        }
    }));
</script>
@endscript