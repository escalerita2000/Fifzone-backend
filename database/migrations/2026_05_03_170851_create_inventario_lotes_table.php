<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventario_lotes', function (Blueprint $table) {
            $table->increments('id_lote');
            $table->unsignedInteger('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->cascadeOnDelete();
            $table->string('numero_lote', 80)->nullable();
            $table->integer('cantidad_inicial')->default(0);
            $table->integer('cantidad_actual')->default(0);
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->unsignedInteger('id_compra')->nullable();
            $table->timestampTz('creado_en')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('inventario_lotes'); }
};