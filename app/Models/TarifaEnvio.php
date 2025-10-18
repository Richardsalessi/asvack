<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifaEnvio extends Model
{
    protected $table = 'tarifas_envio';

    protected $fillable = [
        'ciudad', 'barrio', 'costo', 'activo', 'tiempo_estimado',
    ];

    protected $casts = [
        'costo' => 'integer',
        'activo' => 'boolean',
    ];
}
