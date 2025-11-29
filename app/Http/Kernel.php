<?php

// namespace App\Http\Middleware;

// use Closure;
// use Illuminate\Http\Request;
// use Symfony\Component\HttpFoundation\Response;

// class CheckRole
// {
//     /**
//      * Handle an incoming request.
//      */
//     public function handle(Request $request, Closure $next, string $role): Response
//     {
//         $user = $request->user();
        
//         if (!$user) {
//             return response()->json(['message' => 'Unauthenticated'], 401);
//         }

//         // Mapping route ke permission
//         $permissions = [
//             'superadmin' => ['menu', 'promo', 'karir', 'member', 'outlet', 'user', 'galeri'],
//             'admin' => ['menu', 'promo', 'karir', 'member', 'outlet', 'galeri'],
//             'membership' => ['member']
//         ];

//         // Superadmin bisa akses semua
//         if ($user->role === 'superadmin') {
//             return $next($request);
//         }

//         // Cek apakah role user punya akses ke route yang diminta
//         if (in_array($role, $permissions[$user->role] ?? [])) {
//             return $next($request);
//         }

//         return response()->json(['message' => 'Forbidden: Access denied'], 403);
//     }
// }