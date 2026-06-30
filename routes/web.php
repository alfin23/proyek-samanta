<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Grpup route khusus untuk pengguna yang BELUM LOGIN (guest)
Route::middleware('guest')->group(function () {
    // Route Login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    // Route Register
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');
});

// Group route khusus untuk pengguna yang SUDAH LOGIN (authenticated)
Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', 'dashboard')->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::livewire('/category', 'pages::master.category.index')->name('category.index');
    Route::livewire('/product', 'pages::master.product.index')->name('product.index');
    Route::livewire('/supplier', 'pages::master.supplier.index')->name('supplier.index');
    Route::livewire('/product-variants', 'pages::master.product-variants.index')->name('pv.index');
    Route::livewire('/product-variants/add', 'pages::master.product-variants.add')->name('pv.add');
    Route::livewire('/product-variants/edit/{id}', 'pages::master.product-variants.edit')->name('pv.edit');
});