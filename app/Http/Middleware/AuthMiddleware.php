<?php
// app/Http/Middleware/AuthMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    // Ambil token dari header Authorization
    $token = $request->bearerToken();

    if (!$token) {
      return response()->json([
        'success' => false,
        'message' => 'Token tidak ditemukan'
      ], 401);
    }

    try {
      // Decode token
      $decodedToken = base64_decode($token);
      $tokenData = json_decode($decodedToken, true);

      // Validasi token
      if (!isset($tokenData['id']) || !isset($tokenData['time'])) {
        return response()->json([
          'success' => false,
          'message' => 'Token tidak valid'
        ], 401);
      }

      // Optional: Cek expired (24 jam)
      $tokenTime = $tokenData['time'];
      $currentTime = time();
      $tokenAge = $currentTime - $tokenTime;
      $maxAge = 24 * 60 * 60; // 24 jam dalam detik

      if ($tokenAge > $maxAge) {
        return response()->json([
          'success' => false,
          'message' => 'Token sudah kadaluarsa, silakan login kembali'
        ], 401);
      }

      // Simpan user ID di request untuk digunakan di controller
      $request->merge(['user_id' => $tokenData['id']]);

      return $next($request);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Token tidak valid'
      ], 401);
    }
  }
}
