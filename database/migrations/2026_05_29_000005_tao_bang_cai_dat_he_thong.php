<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cau_hinh_he_thong', function (Blueprint $table) {
            $table->id();
            $table->string('khoa')->unique();
            $table->text('gia_tri')->nullable();
            $table->string('nhom')->default('chung');
            $table->string('nhan');
            $table->string('loai')->default('text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cau_hinh_he_thong');
    }
};
