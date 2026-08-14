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
        Schema::table('lich_su_diems', function (Blueprint $table) {
            $table->integer('diem_con_lai')
                ->nullable()
                ->after('so_diem');

            $table->timestamp('ngay_het_han')
                ->nullable()
                ->after('diem_con_lai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lich_su_diems', function (Blueprint $table) {
            $table->dropColumn([
                'diem_con_lai',
                'ngay_het_han',
            ]);
        });
    }
};