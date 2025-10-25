<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imagen extends Model
{
    use HasFactory;

    protected $table = 'imagenes';
    protected $fillable = ['producto_id', 'ruta', 'contenido'];

    protected $appends = ['url'];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function getUrlAttribute()
    {
        // Si tiene contenido base64 en BD, lo devolvemos como data:image
        if (!empty($this->attributes['contenido'])) {
            return 'data:image/jpeg;base64,' . $this->attributes['contenido'];
        }

        // Si no tiene contenido, pero sí ruta (por compatibilidad futura)
        if (!empty($this->attributes['ruta'])) {
            return asset('images/' . $this->attributes['ruta']);
        }

        // Si no hay nada, devolvemos un placeholder inline
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64">
                <rect width="100%" height="100%" fill="#e5e7eb"/>
                <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
                      font-family="sans-serif" font-size="10" fill="#6b7280">Sin imagen</text>
             </svg>'
        );
    }
}
