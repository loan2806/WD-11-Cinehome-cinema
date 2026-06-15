<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ghe_ngois', function (Blueprint $table) {
            // ID nhóm ghế couple (2 ghế cùng group_id là 1 cặp)
            $table->string('couple_group_id', 50)->nullable()->after('cot')->index();
        });
    }

    public function down(): void
    {
        Schema::table('ghe_ngois', function (Blueprint $table) {
            $table->dropColumn('couple_group_id');
        });
    }
};
