<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ejercicio extends Model
{
    use SoftDeletes;

    protected $table = 'ejercicios';

    protected $fillable = [
        'nombre',
        'grupo_muscular',
        'dificultad',
        'equipamiento',
        'series',
        'repeticiones',
        'descripcion',
        'imagen_url',
        'is_deleted',
        'deleted_by',
        'deleted_at'
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id_usuario');
    }
}
