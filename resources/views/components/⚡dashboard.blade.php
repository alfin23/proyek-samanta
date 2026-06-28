<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="p-8 max-w-4xl mx-auto">
    <div class="hero bg-base-100 p-8 rounded-2xl shadow-sm">
        <div class="hero-content text-center">
            <div class="max-w-md">
                <h1 class="text-3xl font-bold">Halo, {{ Auth::user()->name }}!</h1>
                <p class="py-4 text-base-content/70">Anda berhasil login ke sistem menggunakan otentikasi manual Laravel
                    13.</p>
            </div>
        </div>
    </div>
</div>
