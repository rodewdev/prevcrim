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
        Schema::create('delincuente_familiar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delincuente_id');
            $table->unsignedBigInteger('familiar_id');
            $table->string('parentesco');
            $table->timestamps();

            $table->foreign('delincuente_id')->references('id')->on('delincuentes')->onDelete('cascade');
            $table->foreign('familiar_id')->references('id')->on('delincuentes')->onDelete('cascade');
            $table->unique(['delincuente_id', 'familiar_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delincuente_familiar');
    }
};
