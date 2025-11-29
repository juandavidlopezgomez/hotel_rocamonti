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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // Ejemplo: created_reservation, updated_room_status
            $table->string('model_type')->nullable(); // Clase del modelo afectado
            $table->unsignedBigInteger('model_id')->nullable(); // ID del registro afectado
            $table->json('details')->nullable(); // Detalles adicionales
            $table->string('ip_address', 45)->nullable(); // Soporte para IPv6
            $table->timestamps();

            // Índices para mejorar performance de consultas
            $table->index('user_id');
            $table->index('action');
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
