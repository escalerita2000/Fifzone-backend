<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ventas', function (Blueprint $table) {
            $table->increments('id_venta');
            $table->string('numero_factura', 30)->unique()->nullable();
            $table->unsignedInteger('id_cliente')->nullable();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->nullOnDelete();
            $table->unsignedInteger('id_usuario');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->timestampTz('fecha')->useCurrent();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento_pct', 5, 2)->default(0);
            $table->decimal('descuento_valor', 14, 2)->default(0);
            $table->decimal('impuesto_pct', 5, 2)->default(0);
            $table->decimal('impuesto_valor', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('estado', 20)->default('completada');
            $table->string('canal', 20)->default('tienda');
            $table->text('notas')->nullable();
            $table->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('ventas'); }
};