<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rap_chieu_phims', function (Blueprint $table) {
            $table->string('vi_do')->nullable()->after('hinh_anh');
            $table->string('kinh_do')->nullable()->after('vi_do');
        });
    }

    public function down(): void
    {
        Schema::table('rap_chieu_phims', function (Blueprint $table) {
            $table->dropColumn(['vi_do', 'kinh_do']);
        });
    }
};