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

        // 🌟 BỔ SUNG GIÁ TRỊ MẶC ĐỊNH AN TOÀN TRÁNH BỊ RỖNG CỘT SQL
        $permissionsTable          = !empty($tableNames['permissions']) ? $tableNames['permissions'] : 'permissions';
        $rolesTable                = !empty($tableNames['roles']) ? $tableNames['roles'] : 'roles';
        $modelHasPermissionsTable  = !empty($tableNames['model_has_permissions']) ? $tableNames['model_has_permissions'] : 'model_has_permissions';
        $modelHasRolesTable        = !empty($tableNames['model_has_roles']) ? $tableNames['model_has_roles'] : 'model_has_roles';
        $roleHasPermissionsTable   = !empty($tableNames['role_has_permissions']) ? $tableNames['role_has_permissions'] : 'role_has_permissions';

        $permissionPivotKey = !empty($columnNames['permission_pivot_key']) ? $columnNames['permission_pivot_key'] : 'permission_id';
        $rolePivotKey       = !empty($columnNames['role_pivot_key']) ? $columnNames['role_pivot_key'] : 'role_id';
        $modelMorphKey      = !empty($columnNames['model_morph_key']) ? $columnNames['model_morph_key'] : 'model_id';

        // 1. Tạo bảng lưu danh sách các quyền hạn (permissions)
        if (!Schema::hasTable($permissionsTable)) {
            Schema::create($permissionsTable, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');       
                $table->string('guard_name'); 
                $table->string('description')->nullable(); 
                $table->timestamps();

                $table->unique(['name', 'guard_name']);
            });
        }

        // 2. Tạo bảng lưu danh sách các vai trò (roles)
        if (!Schema::hasTable($rolesTable)) {
            Schema::create($rolesTable, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');       
                $table->string('guard_name'); 
                $table->timestamps();

                $table->unique(['name', 'guard_name']);
            });
        }

        // 3. Tạo bảng trung gian gán quyền trực tiếp cho người dùng (model_has_permissions)
        if (!Schema::hasTable($modelHasPermissionsTable)) {
            Schema::create($modelHasPermissionsTable, function (Blueprint $table) use ($permissionsTable, $permissionPivotKey, $modelMorphKey) {
                $table->unsignedBigInteger($permissionPivotKey);
                $table->string('model_type');
                $table->unsignedBigInteger($modelMorphKey);
                
                $table->index([$modelMorphKey, 'model_type'], 'nd_quyens_model_index');

                $table->foreign($permissionPivotKey)
                    ->references('id') 
                    ->on($permissionsTable)
                    ->onDelete('cascade');

                $table->primary([$permissionPivotKey, $modelMorphKey, 'model_type'], 'nd_quyens_pk');
            });
        }

        // 4. Tạo bảng trung gian gán vai trò cho người dùng (model_has_roles)
        if (!Schema::hasTable($modelHasRolesTable)) {
            Schema::create($modelHasRolesTable, function (Blueprint $table) use ($rolesTable, $rolePivotKey, $modelMorphKey) {
                $table->unsignedBigInteger($rolePivotKey);
                $table->string('model_type');
                $table->unsignedBigInteger($modelMorphKey);

                $table->index([$modelMorphKey, 'model_type'], 'nd_vaitros_model_index');

                $table->foreign($rolePivotKey)
                    ->references('id') 
                    ->on($rolesTable)
                    ->onDelete('cascade');

                $table->primary([$rolePivotKey, $modelMorphKey, 'model_type'], 'nd_vaitros_pk');
            });
        }

        // 5. Tạo bảng liên kết giữa Vai trò và Quyền hạn (role_has_permissions)
        if (!Schema::hasTable($roleHasPermissionsTable)) {
            Schema::create($roleHasPermissionsTable, function (Blueprint $table) use ($permissionsTable, $rolesTable, $permissionPivotKey, $rolePivotKey) {
                $table->unsignedBigInteger($permissionPivotKey);
                $table->unsignedBigInteger($rolePivotKey);

                $table->foreign($permissionPivotKey)
                    ->references('id')
                    ->on($permissionsTable)
                    ->onDelete('cascade');

                $table->foreign($rolePivotKey)
                    ->references('id')
                    ->on($rolesTable)
                    ->onDelete('cascade');

                $table->primary([$permissionPivotKey, $rolePivotKey], 'vaitro_quyens_pk');
            });
        }

        // Xóa bộ nhớ đệm phân quyền nếu có
        if (app()->has('cache')) {
            app('cache')
                ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
                ->forget(config('permission.cache.key', 'spatie.permission.cache'));
        }
    }

    /**
     * Đảo ngược quy trình migration (Xóa bảng khi rollback)
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        $permissionsTable         = !empty($tableNames['permissions']) ? $tableNames['permissions'] : 'permissions';
        $rolesTable               = !empty($tableNames['roles']) ? $tableNames['roles'] : 'roles';
        $modelHasPermissionsTable = !empty($tableNames['model_has_permissions']) ? $tableNames['model_has_permissions'] : 'model_has_permissions';
        $modelHasRolesTable       = !empty($tableNames['model_has_roles']) ? $tableNames['model_has_roles'] : 'model_has_roles';
        $roleHasPermissionsTable  = !empty($tableNames['role_has_permissions']) ? $tableNames['role_has_permissions'] : 'role_has_permissions';

        Schema::dropIfExists($roleHasPermissionsTable);
        Schema::dropIfExists($modelHasRolesTable);
        Schema::dropIfExists($modelHasPermissionsTable);
        Schema::dropIfExists($rolesTable);
        Schema::dropIfExists($permissionsTable);
    }
};