<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('food_invoices')) {
            return;
        }

        Schema::table('food_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('food_invoices', 'payos_order_code')) {
                $table->unsignedBigInteger('payos_order_code')->nullable()->after('change_amount');
            }

            if (! Schema::hasColumn('food_invoices', 'payos_qr_code')) {
                $table->text('payos_qr_code')->nullable()->after('payos_order_code');
            }

            if (! Schema::hasColumn('food_invoices', 'payos_checkout_url')) {
                $table->text('payos_checkout_url')->nullable()->after('payos_qr_code');
            }

            if (! Schema::hasColumn('food_invoices', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('payos_checkout_url');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('food_invoices')) {
            return;
        }

        Schema::table('food_invoices', function (Blueprint $table) {
            foreach (['expires_at', 'payos_checkout_url', 'payos_qr_code', 'payos_order_code'] as $column) {
                if (Schema::hasColumn('food_invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
