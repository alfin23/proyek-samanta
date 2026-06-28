<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="p-4 md:p-8 max-w-7xl mx-auto space-y-6">
    {{-- You must be the change you wish to see in the world. - Mahatma Gandhi --}}

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
        <h2 class="text-xl font-bold border-b pb-4 mb-6">Add New Supplier</h2>

        <form class="space-y-6">
            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-semibold">Supplier Name</span>
                </label>
                <input type="text" placeholder="Type supplier name here..." class="input input-bordered w-full" />
            </div>

            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-semibold">Contact / Phone Number</span>
                </label>
                <input type="text" placeholder="Type contact number here..." class="input input-bordered w-full" />
            </div>

            <div class="flex justify-end gap-2 border-t pt-4">
                <a href="{{ route('supplier.index') }}" wire:navigate class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary px-6">Save</button>
            </div>
        </form>
    </div>
</div>