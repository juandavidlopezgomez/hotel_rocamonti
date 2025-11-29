<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago'); // 'wompi', 'efectivo', 'transferencia', etc.
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'cancelado'])->default('pendiente');
            $table->string('codigo_transaccion')->nullable();
            $table->text('detalles')->nullable(); // JSON con detalles adicionales
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
