<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de');
            $table->text('noi_dung');
            $table->enum('loai', ['info', 'success', 'warning', 'danger'])->default('info');
            $table->enum('doi_tuong', ['admin'])->default('admin');
            $table->boolean('da_doc')->default(false);
            $table->timestamp('thoi_gian_xuat_ban')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
