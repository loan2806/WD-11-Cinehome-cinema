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
        if (!Schema::hasTable('the_loais')) {
            Schema::create('the_loais', function (Blueprint $table) {
                $table->id();
                $table->string('ten_the_loai');
                
                // ĐÃ BỔ SUNG: Cột slug bắt buộc có để lưu trữ dữ liệu từ Seeder
                $table->string('slug')->unique(); 
                
                $table->text('mo_ta')->nullable();
                $table->tinyInteger('trang_thai')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('the_loais');
    }
};