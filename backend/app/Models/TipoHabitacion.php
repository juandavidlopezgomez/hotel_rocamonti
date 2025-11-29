<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoHabitacion extends Model
{
    protected $table = 'tipo_habitacions';

    protected $fillable = [
        'nombre',
        'descripcion_camas',
        'capacidad_adultos',
        'capacidad_ninos',
        'precio_base',
        'es_apartamento',
        'metros_cuadrados',
        'amenidades',
        'imagen_url',
        'imagenes',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'capacidad_adultos' => 'integer',
        'capacidad_ninos' => 'integer',
        'metros_cuadrados' => 'integer',
        'es_apartamento' => 'boolean',
        'amenidades' => 'array',
        'imagenes' => 'array',
    ];

    public function habitaciones(): HasMany
    {
        return $this->hasMany(Habitacion::class, 'tipo_habitacion_id', 'id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'tipo_habitacion_id', 'id');
    }
}
