<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ve_xem_phims', function (Blueprint $table) {
            if (!Schema::hasColumn('ve_xem_phims', 'food_items')) {
                $table->json('food_items')->nullable()->after('trang_thai');
            }

            if (!Schema::hasColumn('ve_xem_phims', 'payment_method')) {
                $table->string('payment_method', 20)
                    ->nullable()
                    ->after('food_items');
            }

            if (!Schema::hasColumn('ve_xem_phims', 'received_amount')) {
                $table->decimal('received_amount', 15, 2)
                    ->default(0)
                    ->after('payment_method');
            }

            if (!Schema::hasColumn('ve_xem_phims', 'change_amount')) {
                $table->decimal('change_amount', 15, 2)
                    ->default(0)
                    ->after('received_amount');
            }

            if (!Schema::hasColumn('ve_xem_phims', 'seat_total')) {
                $table->decimal('seat_total', 15, 2)
                    ->default(0)
                    ->after('change_amount');
            }

            if (!Schema::hasColumn('ve_xem_phims', 'food_total')) {
                $table->decimal('food_total', 15, 2)
                    ->default(0)
                    ->after('seat_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ve_xem_phims', function (Blueprint $table) {
            $columns = [
                'payment_method',
                'received_amount',
                'change_amount',
                'seat_total',
                'food_total',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('ve_xem_phims', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};