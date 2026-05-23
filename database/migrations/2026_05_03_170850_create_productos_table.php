<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id_producto');
            $table->string('nombre', 200);
            $table->string('sku', 60)->unique();
            $table->string('codigo_barras', 60)->unique()->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('id_categoria')->nullable();
            $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->nullOnDelete();
            $table->unsignedInteger('id_marca')->nullable();
            $table->foreign('id_marca')->references('id_marca')->on('marcas')->nullOnDelete();
            $table->string('unidad_medida', 30)->default('unidad');
            $table->decimal('contenido_neto', 10, 2)->nullable();
            $table->decimal('precio_costo', 14, 2)->default(0);
            $table->decimal('precio_venta', 14, 2)->default(0);
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(5);
            $table->string('imagen_url', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('productos'); }
};