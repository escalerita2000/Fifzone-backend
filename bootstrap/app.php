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
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn(Request $r, $e) => $r->is('api/*'));

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