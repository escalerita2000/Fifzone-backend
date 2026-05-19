<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('compra_items', function (Blueprint $table) {
            $table->increments('id_item');
            $table->unsignedInteger('id_compra');
            $table->foreign('id_compra')->references('id_compra')->on('compras')->cascadeOnDelete();
            $table->unsignedInteger('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 14, 2);
            $table->decimal('subtotal', 14, 2);
        });
    }
    public function down(): void { Schema::dropIfExists('compra_items'); }
};