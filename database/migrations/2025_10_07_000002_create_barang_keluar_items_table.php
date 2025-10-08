<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_keluar_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_keluar_id');
            $table->unsignedBigInteger('barang_id');
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('barang_keluar_id')->references('id')->on('barang_keluar')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_keluar_items');
    }
};


