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
        // Verificar si la tabla y columna existen antes de agregar
        if (Schema::hasTable('delincuentes') && !Schema::hasColumn('delincuentes', 'comuna_id')) {
            Schema::table('delincuentes', function (Blueprint $table) {
                $table->foreignId('comuna_id')->nullable()->constrained('comunas')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar si la tabla y columna existen antes de eliminar
        if (Schema::hasTable('delincuentes') && Schema::hasColumn('delincuentes', 'comuna_id')) {
            Schema::table('delincuentes', function (Blueprint $table) {
                $table->dropForeign(['comuna_id']);
                $table->dropColumn('comuna_id');
            });
        }
    }
};
