<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OutletController extends Controller
{
  public function index()
  {
    $outlets = Outlet::all();

    return response()->json([
      'success' => true,
      'data' => $outlets
    ]);
  }

  public function show($id)
  {
    try {
      $outlet = Outlet::findOrFail($id);

      return response()->json([
        'success' => true,
        'data' => $outlet
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Outlet tidak ditemukan'
      ], 404);
    }
  }

  public function store(Request $request)
  {
    Log::info("Request store outlet:", $request->all());

    try {
      $validated = $request->validate([
        'location' => 'required|string|max:255',
        'link'     => 'required|url'
      ]);

      $outlet = Outlet::create($validated);

      return response()->json([
        'success' => true,
        'message' => 'Outlet berhasil ditambahkan',
        'data' => $outlet
      ], 201);
    } catch (\Exception $e) {
      Log::error("Error store outlet: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal menambahkan outlet'
      ], 500);
    }
  }

  public function update(Request $request, $id)
  {
    Log::info("Request update outlet:", $request->all());

    try {
      $validated = $request->validate([
        'location' => 'required|string|max:255',
        'link'     => 'required|url'
      ]);

      $outlet = Outlet::findOrFail($id);
      $outlet->update($validated);

      return response()->json([
        'success' => true,
        'message' => 'Outlet berhasil diperbarui',
        'data' => $outlet
      ]);
    } catch (\Exception $e) {
      Log::error("Error update outlet: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal memperbarui outlet'
      ], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $outlet = Outlet::findOrFail($id);
      $outlet->delete();

      return response()->json([
        'success' => true,
        'message' => 'Outlet berhasil dihapus'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Gagal menghapus outlet'
      ], 500);
    }
  }
}
