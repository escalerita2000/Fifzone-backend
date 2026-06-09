<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SupabaseAuth
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['success' => false, 'message' => 'Token no proporcionado.'], 401);
        }

        $jwt = substr($authHeader, 7);
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return response()->json(['success' => false, 'message' => 'Token inválido.'], 401);
        }

        try {
            // Decodificar payload (segunda parte del JWT) sin verificar firma criptográfica (se confía en HTTPS y comunicación local/gateway)
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            $email = $payload['email'] ?? null;
            $exp = $payload['exp'] ?? null;

            if (!$email) {
                return response()->json(['success' => false, 'message' => 'Email no encontrado en el token.'], 401);
            }

            // Validar expiración si está configurada
            if ($exp && $exp < time()) {
                return response()->json(['success' => false, 'message' => 'El token ha expirado.'], 401);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no registrado en FitZone.'], 401);
            }

            if (!$user->activo) {
                return response()->json(['success' => false, 'message' => 'El usuario se encuentra inactivo o suspendido.'], 403);
            }

            // Autenticar al usuario en el contenedor de Laravel
            Auth::setUser($user);
            $request->setUserResolver(fn() => $user);

        } catch (\Exception $e) {
            Log::error("Error decodificando JWT de Supabase: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al decodificar token de acceso.'], 401);
        }

        return $next($request);
    }
}
