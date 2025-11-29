<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitacions', function (Blueprint $table) {
            $table->id();
            $table->string('numero_habitacion')->unique();
            $table->foreignId('tipo_habitacion_id')->constrained('tipo_habitacions')->onDelete('cascade');
            $table->integer('piso');
            $table->enum('estado', ['disponible', 'ocupada', 'mantenimiento'])->default('disponible');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habitacions');
    }
};
