<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ve_xem_phim_ghes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ve_xem_phim_id')
                ->constrained('ve_xem_phims')
                ->cascadeOnDelete();

            $table->string('ma_ghe', 20);

            /*
             * Token riêng được đưa vào QR.
             * Không nên đưa trực tiếp dữ liệu nhạy cảm vào QR.
             */
            $table->string('ma_qr', 100)->unique();

            $table->string('trang_thai', 30)
                ->default('chua_su_dung');

            $table->timestamp('checked_in_at')->nullable();

            $table->foreignId('checked_in_by')
                ->nullable()
                ->constrained('nguoi_dungs')
                ->nullOnDelete();

            $table->timestamps();

            /*
             * Một ghế chỉ xuất hiện một lần trong cùng một vé.
             */
            $table->unique(
                ['ve_xem_phim_id', 'ma_ghe'],
                've_ghe_unique'
            );

            $table->index([
                've_xem_phim_id',
                'trang_thai',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ve_xem_phim_ghes');
    }
};