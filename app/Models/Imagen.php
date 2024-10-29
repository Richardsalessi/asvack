<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imagen extends Model
{
    use HasFactory;

    protected $table = 'imagenes'; // Especificar el nombre correcto de la tabla

    protected $fillable = ['producto_id', 'ruta', 'contenido'];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
