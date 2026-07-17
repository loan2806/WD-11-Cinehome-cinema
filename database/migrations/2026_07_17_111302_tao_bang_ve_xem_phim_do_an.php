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
        Schema::create('ve_xem_phim_do_an', function (Blueprint $table) {
            $table->id();
            
            // Khóa ngoại liên kết tới bảng vé xem phim
            $table->foreignId('ve_xem_phim_id')
                  ->constrained('ve_xem_phims')
                  ->cascadeOnDelete();
                  
            // Khóa ngoại liên kết tới bảng đồ ăn (foods)
            $table->foreignId('food_id')
                  ->constrained('foods')
                  ->cascadeOnDelete();
                  
            // Số lượng đồ ăn/nước uống đặt kèm
            $table->integer('so_luong')->default(1);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ve_xem_phim_do_an');
    }
};