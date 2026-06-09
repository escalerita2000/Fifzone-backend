<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rutina extends Model
{
    use SoftDeletes;

    protected $table = 'rutinas';
    
    // La tabla rutinas en la base de datos no tiene columna updated_at, solo created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'duracion',
        'nivel',
        'imagen',
        'ejercicios',
        'id_usuario',
        'id_coach',
        'sent_at',
        'deleted_at',
        'deleted_by',
        'is_deleted',
    ];

    protected $casts = [
        'ejercicios' => 'array',
        'is_deleted' => 'boolean',
        'sent_at'    => 'datetime',
    ];

    public $timestamps = true;

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'rutina_usuario', 'id_rutina', 'id_usuario')
                    ->withPivot('asignado_at', 'activo')
                    ->withTimestamps();
    }

    public function usuarioDirecto()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'id_coach', 'id_usuario');
    }
}
