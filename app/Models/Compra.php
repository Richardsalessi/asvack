<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'producto_id', 
        'cantidad', 
        'precio_total', 
        'telefono', 
        'ciudad', 
        'barrio', 
        'direccion'
    ];

    /**
     * Relación con el modelo User.
     * Cada compra pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el modelo Producto.
     * Cada compra está asociada a un producto.
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
