<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Envio extends Model
{
    protected $table = 'envios';

    protected $fillable = [
        'orden_id',
        'transportadora',
        'numero_guia',
        'tipo_envio',
        'estado_envio',
        'costo_envio',
        'fecha_envio',
        'label_url',
        'notas',
    ];

    protected $casts = [
        'costo_envio' => 'integer',
        'fecha_envio' => 'datetime',
    ];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class);
    }
}
