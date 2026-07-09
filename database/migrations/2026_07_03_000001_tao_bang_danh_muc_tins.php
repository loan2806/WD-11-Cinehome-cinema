<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_tins', function (Blueprint $table) {
            $table->id();
            $table->string('ten_danh_muc');
            $table->string('slug')->unique();
            $table->text('mo_ta')->nullable();
            $table->string('icon')->nullable();
            $table->string('mau_sac')->default('#d99a32');
            $table->tinyInteger('thu_tu')->default(0);
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_tins');
    }
};
