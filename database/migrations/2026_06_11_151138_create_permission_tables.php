<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Thực hiện tạo cấu trúc các bảng phân quyền Tiếng Việt
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        if (empty($tableNames)) {
            throw new \Exception('Lỗi: Chưa tải được cấu hình từ config/permission.php. Hãy chạy [php artisan config:clear] rồi thử lại.');
        }

        // 1. Tạo bảng lưu danh sách các quyền hạn (quyens)
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');       
            $table->string('guard_name'); 
            $table->string('description')->nullable(); 
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // 2. Tạo bảng lưu danh sách các vai trò (vai_tros)
        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');       
            $table->string('guard_name'); 
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // 3. Tạo bảng trung gian gán quyền trực tiếp cho người dùng (nguoi_dung_quyens)
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->unsignedBigInteger($columnNames['permission_pivot_key']);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'nd_quyens_model_index');

            $table->foreign($columnNames['permission_pivot_key'])
                ->references('id') 
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary([$columnNames['permission_pivot_key'], $columnNames['model_morph_key'], 'model_type'], 'nd_quyens_pk');
        });

        // 4. Tạo bảng trung gian gán vai trò cho người dùng (nguoi_dung_vai_tros)
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->unsignedBigInteger($columnNames['role_pivot_key']);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);

            $table->index([$columnNames['model_morph_key'], 'model_type'], 'nd_vaitros_model_index');

            $table->foreign($columnNames['role_pivot_key'])
                ->references('id') 
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$columnNames['role_pivot_key'], $columnNames['model_morph_key'], 'model_type'], 'nd_vaitros_pk');
        });

        // 5. Tạo bảng liên kết giữa Vai trò và Quyền hạn (vai_tro_quyens)
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
            $table->unsignedBigInteger($columnNames['permission_pivot_key']);
            $table->unsignedBigInteger($columnNames['role_pivot_key']);

            $table->foreign($columnNames['permission_pivot_key'])
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($columnNames['role_pivot_key'])
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$columnNames['permission_pivot_key'], $columnNames['role_pivot_key']], 'vaitro_quyens_pk');
        });

        // Xóa bộ nhớ đệm phân quyền
        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Đảo ngược quy trình migration (Xóa bảng khi rollback)
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Lỗi: Chưa tải được cấu hình từ config/permission.php.');
        }

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};