<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('hoa_dons', function (Blueprint $table) {
        $table->id();

        // Nhân viên tạo hóa đơn
        $table->foreignId('nguoi_dung_id')
              ->nullable()
              ->constrained('nguoi_dungs')
              ->nullOnDelete();

        $table->decimal('tong_tien', 10, 2)->default(0);

        $table->string('trang_thai')->default('cho_xu_ly');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoa_dons');
    }
};
