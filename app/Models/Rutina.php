<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rutina extends Model
{
    protected $table = 'rutinas';
    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion',
        'nivel',
        'id_usuario',
    ];
    public $timestamps = true;
}
