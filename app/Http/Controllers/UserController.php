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
    Log::info("Request store user:", $request->all());

    try {
      $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'role' => 'required|in:admin,user,staff',
        'password' => 'required|string|min:6',
        'status' => 'required|in:active,inactive'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'error' => 'Validasi gagal',
          'errors' => $validator->errors()
        ], 422);
      }

      $user = new Users();
      $user->name = $request->name;
      $user->email = $request->email;
      $user->role = $request->role;
      $user->password = Hash::make($request->password);
      $user->status = $request->status;
      $user->save();

      Log::info('User berhasil dibuat: ' . $user->id);
      return response()->json([
        'success' => true,
        'message' => "User berhasil dibuat",
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
      Log::info("Updating user with ID: $id");

      $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:users,email,' . $id,
        'role' => 'sometimes|required|in:admin,user,staff',
        'password' => 'sometimes|nullable|string|min:6',
        'status' => 'sometimes|required|in:active,inactive'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'error' => 'Validasi gagal',
          'errors' => $validator->errors()
        ], 422);
      }

      if ($request->has('name')) {
        $user->name = $request->name;
      }

      if ($request->has('email')) {
        $user->email = $request->email;
      }

      if ($request->has('role')) {
        $user->role = $request->role;
      }

      if ($request->has('status')) {
        $user->status = $request->status;
      }

      if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
      }

      $user->save();

      return response()->json([
        'success' => true,
        'data' => [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
          'role' => $user->role,
          'status' => $user->status
        ],
        'message' => 'User berhasil diperbarui'
      ]);
    } catch (\Exception $e) {
      Log::error("Update user failed: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Gagal mengupdate user'
      ], 500);
    }
  }

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
