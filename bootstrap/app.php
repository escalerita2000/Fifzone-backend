<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Desactivar HandleCors de Laravel — CORS lo maneja public/index.php
        $middleware->remove(\Illuminate\Http\Middleware\HandleCors::class);

        $middleware->validateCsrfTokens(except: [
            '/api/*',
        ]);

        $middleware->api(append: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
            'supabase.auth' => \App\Http\Middleware\SupabaseAuth::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return $request->is('api/*');
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $r) {
            return response()->json(['success' => false, 'message' => 'No autenticado.'], 401);
        });
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $r) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $e->errors()], 422);
        });
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, Request $r) {
            return response()->json(['success' => false, 'message' => class_basename($e->getModel()).' no encontrado.'], 404);
        });
    })->create();