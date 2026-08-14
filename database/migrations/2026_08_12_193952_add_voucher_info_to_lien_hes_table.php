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
        Schema::table('lien_hes', function (Blueprint $table) {
            $table->foreignId('voucher_id')
                ->nullable()
                ->after('thoi_gian_xu_ly')
                ->constrained('vouchers')
                ->nullOnDelete();

            $table->timestamp('thoi_gian_tang_voucher')
                ->nullable()
                ->after('voucher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lien_hes', function (Blueprint $table) {
            //
        });
    }
};
