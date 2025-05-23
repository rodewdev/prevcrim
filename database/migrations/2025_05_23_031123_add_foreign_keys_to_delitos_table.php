<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delitos', function (Blueprint $table) {
            // Solo agrega la foreign si la columna existe y no tiene ya la foreign
            if (!\Illuminate\Support\Facades\Schema::hasColumn('delitos', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('region_id');
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('delitos', 'institucion_id')) {
                $table->unsignedBigInteger('institucion_id')->nullable()->after('user_id');
            }
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('institucion_id')->references('id')->on('instituciones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('delitos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['institucion_id']);
        });
    }
};