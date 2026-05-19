<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lista_precios', function (Blueprint $table) {
            $table->increments('id_precio');
            $table->unsignedInteger('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->cascadeOnDelete();
            $table->string('tipo_cliente', 30)->default('minorista');
            $table->decimal('precio', 14, 2);
            $table->date('vigencia_desde')->default(now());
            $table->date('vigencia_hasta')->nullable();
            $table->boolean('activo')->default(true);
        });
    }
    public function down(): void { Schema::dropIfExists('lista_precios'); }
};