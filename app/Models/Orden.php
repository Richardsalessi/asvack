<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orden extends Model
{
    protected $table = 'ordenes';

    protected $fillable = [
        'user_id',
        'estado',         // pendiente|pagada|rechazada|fallida
        'subtotal',
        'envio',
        'total',
        'ref_epayco',
        'trx_id',
        'respuesta',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'subtotal' => 'decimal:2',
        'envio'    => 'decimal:2',
        'total'    => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenDetalle::class);
    }
}
