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
        'contacto_whatsapp',
        'user_id',
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
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación de muchos a muchos con las compras.
     * 
     * Un producto puede estar en muchas compras.
     */
    public function compras()
    {
        return $this->belongsToMany(Compra::class)->withPivot('cantidad', 'precio_total');
    }
}
