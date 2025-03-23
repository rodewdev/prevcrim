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
        Schema::create('delincuentes', function (Blueprint $table) {
            $table->id();
            $table->string('rut', 12)->unique();
            $table->string('nombre');
            $table->string('alias')->nullable();
            $table->string('domicilio')->nullable();
            $table->enum('estado', ['P', 'L', 'A'])->default('L'); // P: Preso, L: Libre, A: Orden de arresto
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delincuentes');
    }
};
