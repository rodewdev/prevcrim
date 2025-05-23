<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sectores', function (Blueprint $table) {
            $table->foreignId('comuna_id')->nullable()->constrained('comunas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sectores', function (Blueprint $table) {
            $table->dropForeign(['comuna_id']);
            $table->dropColumn('comuna_id');
        });
    }
};