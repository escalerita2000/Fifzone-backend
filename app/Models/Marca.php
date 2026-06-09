<?php
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
    use SoftDeletes;

    protected $table      = 'marcas';
    protected $primaryKey = 'id_marca';

    protected $fillable = ['nombre', 'pais_origen', 'logo_url', 'activo', 'deleted_at', 'deleted_by', 'is_deleted'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_marca', 'id_marca');
    }
}