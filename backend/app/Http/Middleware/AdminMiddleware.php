<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Check if user is admin
        if (!$user->is_admin) {
            abort(403, 'Unauthorized access.');
        }

        // Check 2FA if enabled
        if (config('auth.2fa_enabled')) {
            if (!session()->has('2fa_verified')) {
                return redirect('/admin/verify-2fa');
            }
        }

        return $next($request);
    }
}
