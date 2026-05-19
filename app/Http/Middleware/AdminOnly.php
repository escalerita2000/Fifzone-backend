<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Solo administradores.'], 403);
        }
        return $next($request);
    }
}