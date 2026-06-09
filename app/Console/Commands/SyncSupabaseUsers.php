<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSupabaseUsers extends Command
{
    protected $signature = 'users:sync-supabase';
    protected $description = 'Sincroniza y repara inconsistencias entre auth.users de Supabase y public.usuarios';

    public function handle()
    {
        $this->info('Iniciando sincronización de usuarios...');

        try {
            // Obtener todos los usuarios del esquema auth de Supabase
            $authUsers = DB::table('auth.users')->get();

            $created = 0;
            $updated = 0;

            foreach ($authUsers as $authUser) {
                // Leer metadatos del usuario
                $metadata = json_decode($authUser->raw_user_meta_data, true) ?? [];
                $nombre = $metadata['name'] ?? ($metadata['full_name'] ?? split_part_fallback($authUser->email));

                // Determinar rol inicial
                $rol = 'user';
                if ($authUser->email === 'admin@fitzone.com') {
                    $rol = 'admin';
                } elseif ($authUser->email === 'coach@test.com' || $authUser->email === 'rondonbarrerow@gmail.com') {
                    $rol = 'coach';
                }

                // Determinar si está activo (correo confirmado)
                $activo = !is_null($authUser->email_confirmed_at);

                // Verificar si ya existe en public.usuarios
                $dbUser = DB::table('usuarios')->where('email', $authUser->email)->first();

                if (!$dbUser) {
                    // Insertar usuario nuevo
                    DB::table('usuarios')->insert([
                        'nombre'        => $nombre,
                        'email'         => $authUser->email,
                        'password_hash' => $authUser->encrypted_password ?? '',
                        'rol'           => $rol,
                        'activo'        => $activo,
                        'created_at'    => $authUser->created_at,
                        'updated_at'    => $authUser->updated_at,
                    ]);
                    $created++;
                } else {
                    // Actualizar si hay inconsistencias (activo, nombre o password)
                    $needsUpdate = false;
                    $updateData = [];

                    if ($dbUser->activo !== $activo) {
                        $updateData['activo'] = $activo;
                        $needsUpdate = true;
                    }
                    if ($dbUser->nombre !== $nombre && $nombre !== '') {
                        $updateData['nombre'] = $nombre;
                        $needsUpdate = true;
                    }
                    if ($authUser->encrypted_password && $dbUser->password_hash !== $authUser->encrypted_password) {
                        $updateData['password_hash'] = $authUser->encrypted_password;
                        $needsUpdate = true;
                    }

                    if ($needsUpdate) {
                        $updateData['updated_at'] = now();
                        DB::table('usuarios')->where('id_usuario', $dbUser->id_usuario)->update($updateData);
                        $updated++;
                    }
                }
            }

            $this->info("Sincronización completada. Usuarios creados: {$created}, Usuarios actualizados: {$updated}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error ejecutando la sincronización: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

// Función auxiliar para obtener nombre basado en email si no hay metadatos
function split_part_fallback($email) {
    $parts = explode('@', $email);
    return $parts[0] ?? 'Usuario';
}
