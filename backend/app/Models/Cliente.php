<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'cedula';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cedula',
        'nombre',
        'apellido',
        'telefono',
        'email',
    ];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'cliente_cedula', 'cedula');
    }
}
