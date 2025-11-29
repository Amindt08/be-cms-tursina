<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
  public function index()
  {
    try {
      $users = Users::select('id', 'name', 'email', 'role', 'status', 'created_at')
        ->orderBy('created_at', 'desc')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $users
      ]);
    } catch (\Exception $e) {
      Log::error("Get users failed: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Gagal mengambil data users'
      ], 500);
    }
  }

  public function show($id)
  {
    try {
      $user = Users::select('id', 'name', 'email', 'role', 'status', 'created_at')
        ->findOrFail($id);

      return response()->json([
        'success' => true,
        'data' => $user
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'User tidak ditemukan'
      ], 404);
    }
  }

  public function store(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'role' => 'required|in:Admin,Superadmin,Membership',
        'status' => 'required|in:active,inactive'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'error' => 'Validasi gagal',
          'errors' => $validator->errors()
        ], 422);
      }

      // Convert role dari Frontend ke DB
      $role = strtolower($request->role);

      $user = new Users();
      $user->name = $request->name;
      $user->email = $request->email;
      $user->role = $role;
      $user->status = $request->status;
      $user->password = Hash::make('123'); // Password default

      $user->save();

      return response()->json([
        'success' => true,
        'message' => "User berhasil dibuat dengan password default '123'",
        'data' => [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'role' => $user->role,
          'status' => $user->status
        ]
      ], 201);
    } catch (\Exception $e) {
      Log::error("Create user failed: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Gagal membuat user'
      ], 500);
    }
  }

  public function update(Request $request, $id)
  {
    try {
      $user = Users::findOrFail($id);

      $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:users,email,' . $id,
        'role' => 'sometimes|required|in:Admin,Superadmin,Membership',
        'status' => 'sometimes|required|in:active,inactive'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'error' => 'Validasi gagal',
          'errors' => $validator->errors()
        ], 422);
      }

      // Update hanya field yang ada di request
      $updateData = [];
      if ($request->has('name')) {
        $updateData['name'] = $request->name;
      }
      if ($request->has('email')) {
        $updateData['email'] = $request->email;
      }
      if ($request->has('role')) {
        $updateData['role'] = strtolower($request->role);
      }
      if ($request->has('status')) {
        $updateData['status'] = $request->status;
      }

      // Update user
      $user->update($updateData);

      return response()->json([
        'success' => true,
        'message' => 'User berhasil diperbarui',
        'data' => [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'role' => $user->role,
          'status' => $user->status
        ]
      ]);
    } catch (\Exception $e) {
      Log::error("Update user failed: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Gagal mengupdate user'
      ], 500);
    }
  }

  // ... method lainnya tetap sama
  public function destroy($id)
  {
    try {
      $user = Users::findOrFail($id);

      $user->delete();

      return response()->json([
        'success' => true,
        'message' => 'User berhasil dihapus'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Gagal menghapus user',
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function updateStatus(Request $request, $id)
  {
    try {

      $validator = Validator::make($request->all(), [
        'status' => 'required|in:active,inactive'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'error' => 'Validasi gagal',
          'errors' => $validator->errors()
        ], 422);
      }

      $user = Users::findOrFail($id);
      $user->status = $request->status;
      $user->save();

      return response()->json([
        'success' => true,
        'data' => [
          'id' => $user->id,
          'status' => $user->status
        ],
        'message' => 'Status user berhasil diperbarui'
      ]);
    } catch (\Exception $e) {
      Log::error("Update status failed: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Gagal mengupdate status user'
      ], 500);
    }
  }

  public function changePassword(Request $request)
  {
    try {
      Log::info($request->all());
      $validator = Validator::make($request->all(), [
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:6',
        'confirm_password' => 'required|string|same:new_password'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'error' => 'Validasi gagal',
          'errors' => $validator->errors()
        ], 422);
      }

      // Get user ID from token
      $token = $request->bearerToken();
      if (!$token) {
        return response()->json([
          'success' => false,
          'error' => 'Token tidak ditemukan'
        ], 401);
      }

      // Decode token to get user ID
      $decodedToken = base64_decode($token);
      $tokenData = json_decode($decodedToken, true);

      if (!isset($tokenData['id'])) {
        return response()->json([
          'success' => false,
          'error' => 'Token tidak valid'
        ], 401);
      }

      $userId = $tokenData['id'];
      $user = Users::find($userId);

      if (!$user) {
        return response()->json([
          'success' => false,
          'error' => 'User tidak ditemukan'
        ], 404);
      }

      // Check current password
      if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
          'success' => false,
          'error' => 'Password saat ini salah'
        ], 422);
      }

      // Update password
      $user->password = Hash::make($request->new_password);
      $user->save();

      return response()->json([
        'success' => true,
        'message' => 'Password berhasil diubah'
      ]);
    } catch (\Exception $e) {
      Log::error("Change password failed: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Gagal mengubah password'
      ], 500);
    }
  }

  public function statistics()
  {
    try {
      $totalUsers = Users::count();
      $activeUsers = Users::where('status', 'active')->count();
      $inactiveUsers = Users::where('status', 'inactive')->count();

      $roleStats = Users::select('role')
        ->selectRaw('COUNT(*) as count')
        ->groupBy('role')
        ->get();

      return response()->json([
        'success' => true,
        'data' => [
          'total_users' => $totalUsers,
          'active_users' => $activeUsers,
          'inactive_users' => $inactiveUsers,
          'role_statistics' => $roleStats
        ]
      ]);
    } catch (\Exception $e) {
      Log::error("Get statistics failed: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Gagal mengambil statistik users'
      ], 500);
    }
  }
}
