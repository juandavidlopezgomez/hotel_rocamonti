<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->string('cliente_cedula');
            $table->foreign('cliente_cedula')->references('cedula')->on('clientes')->onDelete('cascade');
            $table->foreignId('habitacion_id')->constrained('habitacions')->onDelete('cascade');
            $table->date('fecha_entrada');
            $table->date('fecha_salida');
            $table->integer('num_adultos');
            $table->integer('num_ninos');
            $table->decimal('precio_total', 10, 2);
            $table->enum('estado', ['pendiente', 'pagada', 'cancelada'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
