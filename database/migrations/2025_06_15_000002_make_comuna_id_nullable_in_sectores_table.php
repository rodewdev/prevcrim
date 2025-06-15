<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sectores', function (Blueprint $table) {
            // Solo modificar si la columna existe
            if (Schema::hasColumn('sectores', 'comuna_id')) {
                $table->unsignedBigInteger('comuna_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // No revertimos el cambio para evitar pérdida de datos
    }
};
