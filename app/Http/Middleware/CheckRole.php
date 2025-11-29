<?php

// namespace App\Http\Middleware;

// use Closure;
// use Illuminate\Http\Request;
// use App\Models\Users;
// use Symfony\Component\HttpFoundation\Response;

// class CheckRole
// {
//     public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
//     {
//         $token = $request->bearerToken();
        
//         if (!$token) {
//             return response()->json([
//                 'success' => false,
//                 'error' => 'Token tidak ditemukan'
//             ], 401);
//         }

//         try {
//             $decodedToken = base64_decode($token);
//             $tokenData = json_decode($decodedToken, true);

//             if (!isset($tokenData['id'])) {
//                 return response()->json([
//                     'success' => false,
//                     'error' => 'Token tidak valid'
//                 ], 401);
//             }

//             $user = Users::find($tokenData['id']);

//             if (!$user || $user->status !== 'active') {
//                 return response()->json([
//                     'success' => false,
//                     'error' => 'User tidak valid atau tidak aktif'
//                 ], 401);
//             }

//             $request->merge(['auth_user_id' => $user->id]);
//             $request->setUserResolver(function () use ($user) {
//                 return $user;
//             });

//             if (in_array($user->role, $allowedRoles) || $user->role === 'superadmin') {
//                 return $next($request);
//             }

//             return response()->json([
//                 'success' => false,
//                 'error' => 'Akses ditolak'
//             ], 403);

//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'error' => 'Token tidak valid'
//             ], 401);
//         }
//     }
// }