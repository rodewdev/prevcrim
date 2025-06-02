<?php

/**
 * Class RemoveLatLngFromDelincuentesTable
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delincuentes', function (Blueprint $table) {
            $table->dropColumn(['domicilio_lat', 'domicilio_lng', 'ultimo_lugar_lat', 'ultimo_lugar_lng']);
        });
    }

    public function down(): void
    {
        Schema::table('delincuentes', function (Blueprint $table) {
            $table->decimal('domicilio_lat', 10, 7)->nullable();
            $table->decimal('domicilio_lng', 10, 7)->nullable();
            $table->decimal('ultimo_lugar_lat', 10, 7)->nullable();
            $table->decimal('ultimo_lugar_lng', 10, 7)->nullable();
        });
    }
};
