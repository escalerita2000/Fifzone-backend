<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('marcas', function (Blueprint $table) {
            $table->increments('id_marca');
            $table->string('nombre', 100)->unique();
            $table->string('pais_origen', 80)->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->string('sitio_web', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampsTz();
        });
    }
    public function down(): void { Schema::dropIfExists('marcas'); }
};