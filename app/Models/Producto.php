<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'categoria_id',
        'stock',
        'user_id', // Restaurado para evitar errores en la relación
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function imagenes()
    {
        return $this->hasMany(Imagen::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'user_id'); // Restaurado
    }

    public function ordenDetalles()
    {
        return $this->hasMany(\App\Models\OrdenDetalle::class);
    }

}
