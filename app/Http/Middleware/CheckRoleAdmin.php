<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->role->name === 'admin' || auth()->user()->role->name === 'manager') {
            return $next($request);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'You has no permission to access this endpoint'
            ], 403);
        }
    }
}
