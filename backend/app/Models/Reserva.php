<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'cliente_cedula',
        'habitacion_id',
        'fecha_entrada',
        'fecha_salida',
        'num_adultos',
        'num_ninos',
        'precio_total',
        'estado',
    ];

    protected $casts = [
        'precio_total' => 'decimal:2',
        'num_adultos' => 'integer',
        'num_ninos' => 'integer',
        'habitacion_id' => 'integer',
        'fecha_entrada' => 'date',
        'fecha_salida' => 'date',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_cedula', 'cedula');
    }

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id', 'id');
    }

    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class, 'reserva_id', 'id');
    }
}
