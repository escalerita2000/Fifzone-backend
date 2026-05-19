<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('compras', function (Blueprint $table) {
            $table->increments('id_compra');
            $table->unsignedInteger('id_proveedor');
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores');
            $table->unsignedInteger('id_usuario');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
            $table->timestampTz('fecha_orden')->useCurrent();
            $table->timestampTz('fecha_recibido')->nullable();
            $table->string('numero_factura', 60)->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('impuesto_valor', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('estado', 20)->default('pendiente');
            $table->text('notas')->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('compras'); }
};