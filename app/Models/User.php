<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'direccion_predeterminada', // ✅ nuevo campo
    ];

    /**
     * Campos ocultos al serializar
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts (tipos de conversión automática)
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',

            // ✅ Guarda y lee la dirección cifrada como array
            'direccion_predeterminada' => 'encrypted:array',
        ];
    }

    /**
     * Relación: un usuario puede tener muchas órdenes
     */
    public function ordenes()
    {
        return $this->hasMany(\App\Models\Orden::class);
    }
}
