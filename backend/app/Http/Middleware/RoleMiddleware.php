<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userType = $request->user()->user_type;

        if (!in_array($userType, $roles)) {
            return response()->json([
                'message' => 'Forbidden. Required roles: ' . implode(', ', $roles) . '. Your role: ' . $userType
            ], 403);
        }

        return $next($request);
    }
}