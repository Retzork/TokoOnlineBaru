<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyimpan informasi transaksi Midtrans pada pesanan.
     */
    public function up(): void
    {
        Schema::table('order', function (Blueprint $table) {
            if (!Schema::hasColumn('order', 'midtrans_order_id')) {
                $table->string('midtrans_order_id', 64)->nullable()->unique()->after('status');
            }
            if (!Schema::hasColumn('order', 'snap_token')) {
                $table->text('snap_token')->nullable()->after('midtrans_order_id');
            }
            if (!Schema::hasColumn('order', 'midtrans_redirect_url')) {
                $table->text('midtrans_redirect_url')->nullable()->after('snap_token');
            }
            if (!Schema::hasColumn('order', 'payment_type')) {
                $table->string('payment_type', 50)->nullable()->after('midtrans_redirect_url');
            }
            if (!Schema::hasColumn('order', 'transaction_id')) {
                $table->string('transaction_id', 100)->nullable()->after('payment_type');
            }
            if (!Schema::hasColumn('order', 'transaction_status')) {
                $table->string('transaction_status', 50)->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('order', 'fraud_status')) {
                $table->string('fraud_status', 50)->nullable()->after('transaction_status');
            }
            if (!Schema::hasColumn('order', 'paid_at')) {
                $table->dateTime('paid_at')->nullable()->after('fraud_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            foreach ([
                'paid_at',
                'fraud_status',
                'transaction_status',
                'transaction_id',
                'payment_type',
                'midtrans_redirect_url',
                'snap_token',
                'midtrans_order_id',
            ] as $column) {
                if (Schema::hasColumn('order', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
