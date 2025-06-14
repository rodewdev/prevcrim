<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sectores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            /*$table->foreignId('institucion_id')->constrained('instituciones')->onDelete('cascade');
            $table->foreignId('comuna_id')->constrained('comunas')->onDelete('cascade');*/
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sectores');
    }
};
