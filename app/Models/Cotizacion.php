<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'cliente_id',
        'proveedor_id',
        'detalle',
        'estado',
    ];

    /**
     * Relación con el modelo Mensaje.
     */
    public function mensajes()
    {
        return $this->hasMany(Mensaje::class);
    }

    /**
     * Relación con el cliente (usuario que solicitó la cotización).
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Relación con el proveedor (usuario que está respondiendo la cotización).
     */
    public function proveedor()
    {
        return $this->belongsTo(User::class, 'proveedor_id');
    }
}
