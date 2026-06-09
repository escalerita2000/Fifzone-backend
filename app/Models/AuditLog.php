<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id_audit';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'accion',
        'entidad',
        'entidad_id',
        'descripcion',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
