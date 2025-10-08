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
        Schema::create('isp_packing_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('isp_packing_id');
            $table->unsignedBigInteger('barang_id');
            $table->integer('qty_isp')->default(0);
            $table->timestamps();

            $table->foreign('isp_packing_id')->references('id')->on('isp_packing')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_packing_items');
    }
};
