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
        Schema::create('delincuente_delito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delincuente_id')->constrained('delincuentes')->onDelete('cascade');
            $table->foreignId('delito_id')->constrained('delitos')->onDelete('cascade');
            $table->date('fecha_comision');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delincuente_delito');
    }
};
