<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PromoController extends Controller
{
  public function index()
  {
    $promos = Promo::all();

    return response()->json([
      'success' => true,
      'data' => $promos
    ]);
  }

  public function show($id)
  {
    try {
      $promo = Promo::findOrFail($id);

      return response()->json([
        'success' => true,
        'data' => $promo
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Promo tidak ditemukan'
      ], 404);
    }
  }

  public function store(Request $request)
  {
    Log::info("Request store promo:", $request->all());

    try {
      $validated = $request->validate([
        'promo_name' => 'required|string|max:255',
        'image'      => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'status'     => 'required|in:active,inactive'
      ]);

      $promo = new Promo();

      // Upload image
      if ($request->hasFile('image')) {
        $image = $request->file('image');
        $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/promo'), $filename);
        $promo->image = $filename;
      }

      $promo->promo_name = $validated['promo_name'];
      $promo->status     = $validated['status'];
      $promo->save();

      return response()->json([
        'success' => true,
        'message' => 'Promo berhasil ditambahkan',
        'data' => $promo
      ], 201);
    } catch (\Exception $e) {
      Log::error("Error store promo: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal menambahkan promo'
      ], 500);
    }
  }

  public function update(Request $request, $id)
  {
    Log::info("Request update promo:", $request->all());

    try {
      $validated = $request->validate([
        'promo_name' => 'required|string|max:255',
        'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'status'     => 'required|in:active,inactive'
      ]);

      $promo = Promo::findOrFail($id);

      // Upload image baru
      if ($request->hasFile('image')) {

        // Hapus file lama
        if ($promo->image && file_exists(public_path('images/promo/' . $promo->image))) {
          unlink(public_path('images/promo/' . $promo->image));
        }

        $image = $request->file('image');
        $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/promo'), $filename);
        $promo->image = $filename;
      }

      $promo->promo_name = $validated['promo_name'];
      $promo->status     = $validated['status'];
      $promo->save();

      return response()->json([
        'success' => true,
        'message' => 'Promo berhasil diperbarui',
        'data' => $promo
      ]);
    } catch (\Exception $e) {
      Log::error("Error update promo: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal memperbarui promo'
      ], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $promo = Promo::findOrFail($id);

      // Hapus gambar
      if ($promo->image && file_exists(public_path('images/promo/' . $promo->image))) {
        unlink(public_path('images/promo/' . $promo->image));
      }

      $promo->delete();

      return response()->json([
        'success' => true,
        'message' => 'Promo berhasil dihapus'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Promo tidak ditemukan'
      ], 404);
    }
  }
}
