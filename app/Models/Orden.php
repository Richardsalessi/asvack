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
        'envio',            // Costo de envío (decimal)
        'total',
        'ref_epayco',       // Referencia generada para ePayco
        'trx_id',           // ID transacción (opcional)
        'respuesta',        // Respuesta corta del gateway (texto)
        'payload',          // Datos completos JSON del webhook o ePayco
        'datos_envio',      // Información de facturación / envío (JSON)
        'intentos_pago',    // Contador de intentos de pago
        'ultimo_invoice',   // Último invoice generado
    ];

    protected $casts = [
        'payload'       => 'array',
        'datos_envio'   => 'array',     // 👈 Esto permite que Laravel lo lea como array, no como string
        'subtotal'      => 'decimal:2',
        'envio'         => 'decimal:2', // 👈 Valor del costo de envío
        'total'         => 'decimal:2',
        'intentos_pago' => 'integer',
    ];

    /* =============================
     * 🔗 Relaciones
     * ============================= */

    /**
     * Relación con el usuario que realizó la orden.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los productos (detalles de la orden).
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenDetalle::class);
    }

    /**
     * Relación 1:1 con el registro de envío (tabla envios).
     * Se usa el alias 'envioRegistro' para no chocar con el campo 'envio'.
     */
    public function envioRegistro(): HasOne
    {
        return $this->hasOne(Envio::class, 'orden_id');
    }

    /* =============================
     * 🧠 Métodos útiles
     * ============================= */

    /**
     * Determina si la orden ya tiene datos de envío válidos guardados.
     */
    public function tieneEnvioValidado(): bool
    {
        $datos = (array) ($this->datos_envio ?? []);
        return isset($datos['validated']) && $datos['validated'] === true;
    }

    /**
     * Limpia el carrito asociado a esta orden en la sesión (uso después del pago).
     */
    public static function limpiarSesion(): void
    {
        session()->forget(['carrito', 'cart_count', 'orden_pendiente_id']);
    }
}
