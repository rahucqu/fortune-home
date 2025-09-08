<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictAdminFromUserPortal
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in and has admin role
        if ($request->user() && $request->user()->can('access admin panel')) {
            // Redirect admin users to admin panel instead of user portal
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Admin users should use the admin panel.',
                    'redirect' => '/admin',
                ], 403);
            }

            return redirect('/admin')->with('info', 'You have been redirected to the admin panel.');
        }

        return $next($request);
    }
}
