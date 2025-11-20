<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountType
{
    /**
     * Handle an incoming request.
     *x
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Your custom logic goes here
        // For example, checking user roles or permissions

        if ($request->user() && $request->user()->account_type !== 'premium') {
            return redirect('/dashboard')->with('error', 'Unauthorized access.');
        }

        return $next($request); // Proceed to the next middleware or the route handler
    }
}