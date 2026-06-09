<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialCorreo extends Model
{
    protected $table = 'historial_correos';

    protected $fillable = [
        'id_usuario',
        'id_rutina',
        'email',
        'asunto',
        'cuerpo',
        'estado',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function rutina()
    {
        return $this->belongsTo(Rutina::class, 'id_rutina', 'id');
    }
}
