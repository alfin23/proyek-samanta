<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-base-200 min-h-screen">

    <div class="navbar bg-base-100 shadow-md px-4 sm:px-8">
        <div class="navbar-start">
            <a class="btn btn-ghost text-xl font-bold">Internal System</a>
        </div>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1 font-semibold">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                </li>
                <li>
                    <details {{ request()->routeIs(['category.*', 'supplier.*', 'product.*']) ? 'open' : '' }}>
                        <summary>Master</summary>
                        <ul class="p-2 bg-base-100 rounded-t-none z-50 w-40 shadow-lg">
                            <li><a class="{{ request()->routeIs('category.*') ? 'bg-[#ff5861] text-white' : '' }}" href="{{ route('category.index') }}" wire:navigate>Categori</a></li>
                            <li><a class="{{ request()->routeIs('supplier.*') ? 'bg-[#ff5861] text-white' : '' }}" href="{{ route('supplier.index') }}" wire:navigate>Supplier</a></li>
                            <li><a class="{{ request()->routeIs('product.*') ? 'bg-[#ff5861] text-white' : '' }}" href="{{ route('product.index') }}" wire:navigate>Product</a></li>
                        </ul>
                    </details>
                </li>
                <li><a class="{{ request()->routeIs('pv.*') ? 'bg-[#ff5861] text-white' : '' }}" href="{{ route('pv.index') }}" wire:navigate>Product Variants</a></li>
                <li>
                    <details {{ request()->routeIs(['inbound.*', 'outbound.*']) ? 'open' : '' }}>
                        <summary>Item</summary>
                        <ul class="p-2 bg-base-100 rounded-t-none z-50 w-40 shadow-lg">
                            <li>
                                <a class="{{ request()->routeIs('inbound.*') ? 'active' : '' }}">Inbound</a>
                            </li>
                            <li>
                                <a class="{{ request()->routeIs('outbound.*') ? 'active' : '' }}">Outbound</a>
                            </li>
                        </ul>
                    </details>
                </li>
            </ul>
        </div>
        <div class="navbar-end">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-error btn-sm text-white">Keluar</button>
            </form>
        </div>
    </div>

    {{ $slot }}

    @livewireScripts
</body>

</html>