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
        'deleted_at',
        'deleted_by',
        'is_deleted',
    ];

    protected $casts = [
        'ejercicios' => 'array',
        'is_deleted' => 'boolean',
    ];

    public $timestamps = true;
}
