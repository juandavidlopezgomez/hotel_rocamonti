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
        Schema::table('tipo_habitacions', function (Blueprint $table) {
            // Array de 4 imágenes para carrusel
            $table->json('imagenes')->nullable()->after('imagen_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_habitacions', function (Blueprint $table) {
            $table->dropColumn('imagenes');
        });
    }
};
