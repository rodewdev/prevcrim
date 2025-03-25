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
        Schema::create('delitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('codigo_delito_id')->constrained('codigos_delitos')->onDelete('cascade');
            $table->text('descripcion');
            $table->foreignId('sector_id')->constrained('sectores')->onDelete('cascade');
            $table->foreignId('comuna_id')->constrained('comunas')->onDelete('cascade');
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delitos');
    }
};
