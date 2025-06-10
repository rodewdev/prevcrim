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
        // Verificar si la tabla y columnas existen antes de agregar
        if (Schema::hasTable('delitos')) {
            Schema::table('delitos', function (Blueprint $table) {
                if (!Schema::hasColumn('delitos', 'ubicacion')) {
                    $table->string('ubicacion')->nullable()->after('descripcion');
                }
                if (!Schema::hasColumn('delitos', 'ubicacion_lat')) {
                    $table->decimal('ubicacion_lat', 10, 7)->nullable()->after('ubicacion');
                }
                if (!Schema::hasColumn('delitos', 'ubicacion_lng')) {
                    $table->decimal('ubicacion_lng', 10, 7)->nullable()->after('ubicacion_lat');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar si la tabla y columnas existen antes de eliminar
        if (Schema::hasTable('delitos')) {
            Schema::table('delitos', function (Blueprint $table) {
                if (Schema::hasColumn('delitos', 'ubicacion_lng')) {
                    $table->dropColumn('ubicacion_lng');
                }
                if (Schema::hasColumn('delitos', 'ubicacion_lat')) {
                    $table->dropColumn('ubicacion_lat');
                }
                if (Schema::hasColumn('delitos', 'ubicacion')) {
                    $table->dropColumn('ubicacion');
                }
            });
        }
    }
};
