<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habitacion extends Model
{
    protected $table = 'habitacions';

    protected $fillable = [
        'numero_habitacion',
        'tipo_habitacion_id',
        'piso',
        'estado',
    ];

    protected $casts = [
        'piso' => 'integer',
        'tipo_habitacion_id' => 'integer',
    ];

    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipo_habitacion_id', 'id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'habitacion_id', 'id');
    }
}
