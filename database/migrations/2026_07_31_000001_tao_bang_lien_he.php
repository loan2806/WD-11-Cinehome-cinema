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
        Schema::create('lien_hes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->nullable()->constrained('nguoi_dungs')->nullOnDelete();
            $table->string('ho_ten');
            $table->string('email');
            $table->string('so_dien_thoai')->nullable();
            $table->string('chu_de');
            $table->text('noi_dung');
            $table->enum('trang_thai', ['cho_xu_ly', 'dang_xu_ly', 'da_xu_ly'])->default('cho_xu_ly');
            $table->text('phan_hoi')->nullable();
            $table->foreignId('nguoi_xu_ly_id')->nullable()->constrained('nguoi_dungs')->nullOnDelete();
            $table->timestamp('thoi_gian_xu_ly')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lien_hes');
    }
};
