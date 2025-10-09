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
        'estado',
        'subtotal',
        'envio',
        'total',
        'ref_epayco',      // referencia de ePayco (x_ref_payco / invoice usado)
        'trx_id',          // x_transaction_id de ePayco
        'respuesta',       // 'Aprobada' | 'Rechazada' | 'Pendiente'
        'payload',         // JSON completo del webhook
        'datos_envio',     // JSON billing/shipping
        'intentos_pago',   // contador de reintentos (INT)
        'ultimo_invoice',  // último invoice enviado (ej: ORD-45-INT2)
    ];

    protected $casts = [
        'payload'       => 'array',
        'datos_envio'   => 'array',
        'subtotal'      => 'decimal:2',
        'envio'         => 'decimal:2',
        'total'         => 'decimal:2',
        'intentos_pago' => 'integer',
        // 'ultimo_invoice' no requiere cast; es VARCHAR
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
