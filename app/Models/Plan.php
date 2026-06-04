<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $table = 'planes';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'nombre',
        'precio',
        'precio_display',
        'periodo',
        'descripcion',
        'beneficios',
        'color',
        'badge',
        'requiere_coach',
        'is_deleted',
        'deleted_by',
    ];

    protected $casts = [
        'beneficios' => 'array',
        'requiere_coach' => 'boolean',
        'is_deleted' => 'boolean',
    ];
}
