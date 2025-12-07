<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPaymentSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verify SSL/TLS in production
        if (config('app.env') === 'production' && !$request->secure()) {
            return response('HTTPS required for payment processing', 403);
        }

        // Check for valid headers
        if ($request->isMethod('post')) {
            // Additional security checks
            if (!$request->header('X-CSRF-TOKEN') && !$request->header('X-Requested-With')) {
                return response('Invalid request', 403);
            }
        }

        return $next($request);
    }
}
