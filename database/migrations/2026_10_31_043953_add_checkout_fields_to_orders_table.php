<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order', function (Blueprint $table) {
            // Hanya tambahkan kolom yang belum ada
            if (!Schema::hasColumn('order', 'tanggal')) {
                $table->dateTime('tanggal')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            if (Schema::hasColumn('order', 'tanggal')) {
                $table->dropColumn('tanggal');
            }
        });
    }
};
