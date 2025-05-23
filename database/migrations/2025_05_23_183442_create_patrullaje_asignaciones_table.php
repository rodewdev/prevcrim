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
         Schema::create('patrullaje_asignaciones', function (Blueprint $table) {
            $table->id();
           $table->foreignId('comuna_id')->constrained('comunas')->onDelete('cascade');
        $table->unsignedBigInteger('sector_id');
        $table->foreign('sector_id')->references('id')->on('sectores')->onDelete('cascade');            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('prioridad')->default(2); // 1: Alta, 2: Media, 3: Baja
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrullaje_asignaciones');
    }
};
