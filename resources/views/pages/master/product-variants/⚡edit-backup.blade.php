<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductVariant;

new class extends Component
{
    // Menyimpan ID produk yang sedang diedit
    public $productId;
    
    // Properti pendukung struktur form (untuk dibaca di Alpine.js)
    public $initialVariants = [];
    public $errorsBag = [];

    // Mengambil parameter ID dari Route.
    // Karena di index di-groupBy('product_id'), maka parameter {id} ini membawa nilai product_id
    public function mount($id)
    {
        $this->productId = $id;

        // Ambil semua varian yang memiliki product_id tersebut
        $variants = ProductVariant::where('product_id', $this->productId)->get();

        // Jika data tidak ditemukan, batalkan operasi
        if ($variants->isEmpty()) {
            abort(404);
        }

        // Format data menjadi bentuk array agar mudah dibaca oleh Alpine.js
        $this->initialVariants = $variants->map(function ($item) {
            return [
                'id' => $item->id, // Simpan ID database untuk keperluan update/delete nanti
                'color' => $item->color,
                'size' => $item->size,
                'stock' => $item->stock,
            ];
        })->toArray();
    }

    // Computed Property untuk memuat daftar produk (digunakan jika user ingin mengubah produknya)
    #[\Livewire\Attributes\Computed]
    public function products()
    {
        return Product::pluck('name', 'id');
    }

    // Fungsi menerima data dari Alpine.js untuk disimpan ke database
    public function save($productId, $variants)
    {
        // 1. Validasi manual data dinamis dari Alpine.js
        $this->errorsBag = [];
        
        if (empty($productId)) {
            $this->errorsBag['product_id'] = 'The product field is required.';
        }

        foreach ($variants as $index => $variant) {
            if (empty($variant['color'])) {
                $this->errorsBag["variants.{$index}.color"] = 'Required';
            }
            if (empty($variant['size'])) {
                $this->errorsBag["variants.{$index}.size"] = 'Required';
            }
            if ($variant['stock'] === '' || $variant['stock'] < 0) {
                $this->errorsBag["variants.{$index}.stock"] = 'Min 0';
            }
        }

        // Jika ada error validasi, kirim balik ke Alpine dan hentikan proses
        if (!empty($this->errorsBag)) {
            return;
        }

        // 2. Proses sinkronisasi database
        // Ambil semua ID varian lama yang ada di database untuk produk ini
        $existingVariantIds = ProductVariant::where('product_id', $this->productId)->pluck('id')->toArray();
        $updatedVariantIds = [];

        foreach ($variants as $variantData) {
            if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                // UPDATE: Jika varian sudah ada di DB, lakukan update data
                ProductVariant::where('id', $variantData['id'])->update([
                    'product_id' => $productId,
                    'color' => $variantData['color'],
                    'size' => $variantData['size'],
                    'stock' => $variantData['stock'],
                ]);
                $updatedVariantIds[] = $variantData['id'];
            } else {
                // CREATE NEW: Jika user menekan tombol "Add Product Variant" baru di halaman edit
                $newVariant = ProductVariant::create([
                    'product_id' => $productId,
                    'color' => $variantData['color'],
                    'size' => $variantData['size'],
                    'stock' => $variantData['stock'],
                ]);
                $updatedVariantIds[] = $newVariant->id;
            }
        }

        // DELETE: Jika user menghapus item varian di form edit, hapus dari database
        $idsToDelete = array_diff($existingVariantIds, $updatedVariantIds);
        if (!empty($idsToDelete)) {
            ProductVariant::whereIn('id', $idsToDelete)->delete();
        }

        // Update state URL jika seandainya user mengganti pilihan produk utama
        $this->productId = $productId;

        // Berikan sinyal sukses ke Alpine.js browser
        $this->dispatch('saved');
    }
};
?>

<div class="p-4 md:p-8 max-w-7xl mx-auto space-y-6" 
     x-data="variantForm" 
     @saved.window="triggerAlert('Product variants updated successfully!', 'alert-success')">

    <div class="rounded-xl border border-base-200 bg-base-100 shadow-sm p-6 md:p-8">
        <h2 class="text-xl font-bold border-b pb-4 mb-6">Edit Product Variant</h2>

        <!-- Perhatikan wire:submit diganti dengan submit handler Alpine.js -->
        <form class="space-y-6" @submit.prevent="submitForm">
            
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-semibold">Product Name</span>
                </label>
                <select class="select select-bordered w-full" :class="errors['product_id'] ? 'input-error' : ''" x-model="product_id">
                    <option value="">Select Product</option>
                    @foreach ($this->products as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                <template x-if="errors['product_id']">
                    <span class="text-error text-sm mt-1" x-text="errors['product_id']"></span>
                </template>
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
            <svg xmlns="http://w3.org" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-text="alertMessage" class="text-sm font-medium"></span>
        </div>
    </div>
</div>

<!-- KODE ALPINE.JS JAVASCRIPT -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('variantForm', () => ({
            // Ambil data awal dari PHP Livewire menggunakan properti public
            product_id: @js($productId),
            variants: @js($initialVariants),
            
            // Mengambil errors bag yang dikirim balik oleh Livewire
            errors: @entangle('errorsBag'),
            // Toast state
            showAlert: false,
            alertMessage: '',
            alertType: 'alert-success',
            
            addVariant() {
                this.variants.push({color: '',size: '',stock: 0});
            },
            
            removeVariant(index) {
                this.variants.splice(index, 1);
            },
            
            getError(index, field) {
                let errorKey = `variants.${index}.${field}`;
                return this.errors[errorKey] || '';
            },
            
            submitForm() {
                // Panggil method save() milik Livewire dengan melempar data dari Alpine
                this.$wire.save(this.product_id, this.variants);
            },
            
            triggerAlert(message, type) {
                this.alertMessage = message;
                this.alertType = type;
                this.showAlert = true;
                setTimeout(() => {
                    this.showAlert = false;
                }, 3000);
            }
        }));
    });
</script>