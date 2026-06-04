<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar usuarios de prueba con email de coach ficticio
        DB::table('usuarios')->where('email', 'coach@fitzone.com')->delete();

        // Insertar coach real
        DB::table('usuarios')->insertOrIgnore([
            'nombre'        => 'Coach FitZone',
            'email'         => 'rondonbarrerow@gmail.com',
            'password_hash' => Hash::make('fitzone2024'),
            'rol'           => 'coach',
            'activo'        => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('usuarios')->where('email', 'rondonbarrerow@gmail.com')->delete();
    }
};
