<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan part_no di tabel barang unique (jalankan ini dulu jika belum)
        Schema::table('barang', function (Blueprint $table) {
            $table->unique('part_no');
            $table->index('part_no');
        });

        // Tabel orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi', 255)->unique();
            $table->dateTime('delivery_date');
            $table->string('status')->default('pending'); // pending, processing, completed, cancelled
            $table->timestamps();
            
            $table->index('no_transaksi');
            $table->index('status');
        });

        // Tabel order_items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('part_no');
            $table->integer('quantity');
            $table->timestamps();
            
            // Foreign key ke tabel barang menggunakan part_no
            // Ini yang membuat relasi tetap terjaga!
            $table->foreign('part_no')
                  ->references('part_no')
                  ->on('barang')
                  ->onDelete('restrict');
            
            $table->index('order_id');
            $table->index('part_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        
        // Rollback unique constraint
        Schema::table('barang', function (Blueprint $table) {
            $table->dropUnique(['part_no']);
            $table->dropIndex(['part_no']);
        });
    }
};