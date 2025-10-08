<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PullingController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/mobile-dashboard', [DashboardController::class, 'mobile'])->name('mobile.dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('barang/get-data', [BarangController::class, 'getData'])->name('barang.get-data');
    Route::post('/barang/import', [BarangController::class, 'import'])->name('barang.import');
    Route::get('/barang/download-template', [BarangController::class, 'downloadTemplate'])->name('barang.download-template');
    Route::resource('barang', BarangController::class);

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrdersController::class, 'index'])->name('orders.index');
        Route::get('/get-data', [OrdersController::class, 'getData'])->name('orders.get-data');
        Route::post('/import', [OrdersController::class, 'import'])->name('orders.import');
        Route::get('/download-template', [OrdersController::class, 'downloadTemplate'])->name('orders.download-template');
        Route::get('/create', [OrdersController::class, 'create'])->name('orders.create');
        Route::post('/', [OrdersController::class, 'store'])->name('orders.store');
        Route::get('/{id}', [OrdersController::class, 'show'])->name('orders.show');
        Route::delete('/{id}', [OrdersController::class, 'destroy'])->name('orders.destroy');
    });

    
    Route::prefix('barang-masuk')->group(function () {
        Route::get('/', [BarangMasukController::class, 'index'])->name('barang-masuk.index');
        Route::get('/get-data', [BarangMasukController::class, 'getData'])->name('barang-masuk.get-data');
        Route::get('/create', [BarangMasukController::class, 'create'])->name('barang-masuk.create');
        Route::post('/scan', [BarangMasukController::class, 'scanBarang'])->name('barang-masuk.scan');
        Route::post('/', [BarangMasukController::class, 'store'])->name('barang-masuk.store');
        Route::get('/date/{date}/transactions', [BarangMasukController::class, 'getTransactionsByDate'])->name('barang-masuk.transactions-by-date');
        Route::delete('/{id}', [BarangMasukController::class, 'destroy'])->name('barang-masuk.destroy');
        Route::get('/{id}', [BarangMasukController::class, 'show'])->name('barang-masuk.show');
    });

    // Pulling
    Route::prefix('pulling')->group(function () {
        Route::get('/detail/{id}', [PullingController::class, 'detail'])->name('pulling.detail');
        Route::get('/', [\App\Http\Controllers\PullingController::class, 'index'])->name('pulling.index');
        Route::get('/get-data', [\App\Http\Controllers\PullingController::class, 'getData'])->name('pulling.get-data');
        Route::get('/create', [\App\Http\Controllers\PullingController::class, 'create'])->name('pulling.create');
        Route::post('/scan', [\App\Http\Controllers\PullingController::class, 'scan'])->name('pulling.scan');
        Route::post('/submit', [\App\Http\Controllers\PullingController::class, 'submit'])->name('pulling.submit');
        Route::delete('/delete-item/{id}', [\App\Http\Controllers\PullingController::class, 'deleteItem'])->name('pulling.delete-item');
    });

    // ISP Packing
    Route::prefix('packing')->group(function () {
        Route::get('/', [\App\Http\Controllers\IspPackingController::class, 'index'])->name('packing.index');
        Route::get('/get-data', [\App\Http\Controllers\IspPackingController::class, 'getData'])->name('packing.get-data');
        Route::get('/create', [\App\Http\Controllers\IspPackingController::class, 'create'])->name('packing.create');
        Route::get('/get-item-detail', [\App\Http\Controllers\IspPackingController::class, 'getItemDetail'])->name('packing.get-item-detail');
        Route::post('/scan', [\App\Http\Controllers\IspPackingController::class, 'scan'])->name('packing.scan');
        Route::post('/submit', [\App\Http\Controllers\IspPackingController::class, 'submit'])->name('packing.submit');
    });
});

require __DIR__.'/auth.php';
