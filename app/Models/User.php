<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'email',
        'password_hash',
        'rol',
        'activo',
        'ultimo_acceso',
        'plan',
        'id_coach',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activo'            => 'boolean',
            'ultimo_acceso'     => 'datetime',
        ];
    }

    public function sales()
    {
        return $this->hasMany(Venta::class, 'id_usuario', 'id_usuario');
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'id_coach', 'id_usuario');
    }

    public function miembros()
    {
        return $this->hasMany(User::class, 'id_coach', 'id_usuario');
    }
}