<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_habitacions', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion_camas');
            $table->integer('capacidad_adultos');
            $table->integer('capacidad_ninos');
            $table->decimal('precio_base', 10, 2);
            $table->boolean('es_apartamento')->default(false);
            $table->integer('metros_cuadrados')->nullable();
            $table->json('amenidades')->nullable();
            $table->string('imagen_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_habitacions');
    }
};
