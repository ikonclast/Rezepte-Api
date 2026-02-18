<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // via auth:sanctum
        if (!$user || !$user->isAdmin()) {
            // Einheitliche 403-Fehlermeldung im JSON-Format
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Administrator privileges required.'
            ], 403);
        }
        return $next($request);
    }
}
