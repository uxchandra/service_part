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
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom email
            $table->dropColumn('email');
            $table->dropColumn('email_verified_at');
            
            // Tambah kolom username dan role
            $table->string('username')->unique()->after('id');
            $table->foreignId('role_id')->constrained()->after('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore kolom email
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            
            // Hapus kolom username dan role
            $table->dropForeign(['role_id']);
            $table->dropColumn(['username', 'role_id']);
        });
    }
};
