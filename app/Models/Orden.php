<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Orden extends Model
{
    protected $table = 'ordenes';

    protected $fillable = [
        'user_id',
        'estado',
        'subtotal',
        'envio',          // 👈 costo de envío (decimal) — se mantiene
        'total',
        'ref_epayco',
        'trx_id',
        'respuesta',
        'payload',
        'datos_envio',
        'intentos_pago',
        'ultimo_invoice',
    ];

    protected $casts = [
        'payload'       => 'array',
        'datos_envio'   => 'array',
        'subtotal'      => 'decimal:2',
        'envio'         => 'decimal:2',   // 👈 atributo que choca con la relación si se llama igual
        'total'         => 'decimal:2',
        'intentos_pago' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenDetalle::class);
    }

    // ✅ Relación 1:1 con la tabla envios, RENOMBRADA para no chocar con el atributo 'envio'
    public function envioRegistro(): HasOne
    {
        return $this->hasOne(Envio::class, 'orden_id');
    }
}
