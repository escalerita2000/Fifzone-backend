<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('usuarios')->insertOrIgnore([
            'nombre'        => 'Coach FitZone',
            'email'         => 'coach@fitzone.com',
            'password_hash' => Hash::make('coach1234'),
            'rol'           => 'coach',
            'activo'        => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('usuarios')->where('email', 'coach@fitzone.com')->delete();
    }
};
