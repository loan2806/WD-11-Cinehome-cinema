<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm tọa độ và trạng thái phục vụ bản đồ rạp (Leaflet / Haversine).
     */
    public function up(): void
    {
        Schema::table('cinemas', function (Blueprint $table) {
            if (! Schema::hasColumn('cinemas', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('city');
            }
            if (! Schema::hasColumn('cinemas', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (! Schema::hasColumn('cinemas', 'status')) {
                $table->string('status', 20)->default('active')->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cinemas', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('cinemas', 'latitude')) {
                $cols[] = 'latitude';
            }
            if (Schema::hasColumn('cinemas', 'longitude')) {
                $cols[] = 'longitude';
            }
            if (Schema::hasColumn('cinemas', 'status')) {
                $cols[] = 'status';
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
