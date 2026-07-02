<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tin_tucs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('danh_muc_tin_id')->constrained('danh_muc_tins')->onDelete('cascade');
            $table->string('tieu_de');
            $table->string('slug')->unique();
            $table->text('mo_ta_ngan')->nullable();
            $table->longText('noi_dung')->nullable();
            $table->string('hinh_anh')->nullable();
            $table->string('hinh_anh_thumbnail')->nullable();
            $table->boolean('noi_bat')->default(false);
            $table->boolean('trang_thai')->default(true);
            $table->string('tac_gia')->nullable();
            $table->integer('luot_xem')->default(0);
            $table->dateTime('ngay_dang')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tin_tucs');
    }
};
