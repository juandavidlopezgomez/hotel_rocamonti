<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'reserva_id',
        'monto',
        'metodo_pago',
        'estado',
        'codigo_transaccion',
        'detalles',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'reserva_id' => 'integer',
        'detalles' => 'array',
    ];

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id', 'id');
    }
}
