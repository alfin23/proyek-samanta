<?php

use Livewire\Component;
use App\Models\ProductVariant;


new class extends Component
{
    public ProductVariant $id;

    // Opsional: Jika ingin mengganti nama variabel agar lebih enak dibaca (misal: $variant)
    // Anda bisa mengisinya di dalam fungsi mount() seperti ini:
    /*
    public ProductVariant $variant;
    
    public function mount(ProductVariant $id)
    {
        $this->variant = $id;
    }
    */
};
?>

<div>
    {{-- Well begun is half done. - Aristotle --}}
    <h1>{{ $id->product->name }}</h1>
</div>