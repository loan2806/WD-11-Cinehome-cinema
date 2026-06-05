<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rap_chieu_phims', function (Blueprint $table) {
            $table->id();
            $table->string('ten_rap');
            $table->string('dia_chi');
            $table->string('thanh_pho')->nullable();
            $table->string('so_dien_thoai')->nullable();
            $table->string('hinh_anh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rap_chieu_phims');
    }
};