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
        Schema::create('cham_congs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')->constrained('nguoi_dungs')->onDelete('cascade');
            $table->date('ngay');
            $table->time('gio_vao')->nullable();
            $table->time('gio_ra')->nullable();
            $table->decimal('so_gio_lam', 5, 2)->default(0.00);
            $table->decimal('so_gio_tang_ca', 5, 2)->default(0.00);
            $table->boolean('di_muon')->default(false);
            $table->boolean('ve_som')->default(false);
            $table->boolean('nghi_phep')->default(false);
            $table->boolean('nghi_khong_phep')->default(false);
            $table->string('ghi_chu')->nullable();
            $table->timestamps();

            // Đảm bảo không chấm công trùng trong cùng một ngày cho một nhân viên
            $table->unique(['nguoi_dung_id', 'ngay']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cham_congs');
    }
};
