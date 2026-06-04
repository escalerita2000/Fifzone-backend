<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('plan', 20)->nullable()->after('rol');
            $table->unsignedInteger('id_coach')->nullable()->after('plan');

            $table->foreign('id_coach')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['id_coach']);
            $table->dropColumn(['plan', 'id_coach']);
        });
    }
};
