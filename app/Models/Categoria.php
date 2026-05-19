<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table      = 'categorias';
    protected $primaryKey = 'id_categoria';

    protected $fillable = ['nombre', 'slug', 'descripcion', 'id_padre', 'activo'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }

    public function subcategorias()
    {
        return $this->hasMany(Categoria::class, 'id_padre', 'id_categoria');
    }

    public function padre()
    {
        return $this->belongsTo(Categoria::class, 'id_padre', 'id_categoria');
    }
}