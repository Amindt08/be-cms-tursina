<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CareerController extends Controller
{
  public function index()
  {
    $karir = Career::all();

    return response()->json([
      'success' => true,
      'data' => $karir
    ]);
  }

  public function show($id)
  {
    try {
      $karir = Career::findOrFail($id);

      return response()->json([
        'success' => true,
        'data' => $karir
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Karir tidak ditemukan'
      ], 404);
    }
  }

  public function store(Request $request)
  {
    Log::info("Request store career:", $request->all());

    try {
      $validated = $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'required|string'
      ]);

      $karir = new Career();

      if ($request->hasFile('image')) {
        $image = $request->file('image');
        $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/karir'), $filename);
        $karir->image = $filename;
      }

      $karir->description = $validated['description'];
      $karir->save();

      Log::info('Gambar berhasil di unggah:' . $karir->image);
      return response()->json([
        'success' => true,
        'message' => "Karir berhasil dibuat",
        'data' => $karir
      ], 201);
    } catch (\Exception $e) {
      Log::error("Gambar gagal di unggah:" . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Gagal mengunggah image'
      ], 500);
    }
  }

  public function update(Request $request, $id)
  {
    try {
      $karir = Career::findOrFail($id);
      Log::info("Updating item with ID: $id");

      $validated = $request->validate([
        'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'sometimes|string'
      ]);

      if ($request->hasFile('image')) {
        // Delete old image if exists
        if ($karir->image) {
          $oldPath = public_path('images/karir/' . $karir->image);
          if (file_exists($oldPath)) {
            unlink($oldPath);
          }
        }

        $image = $request->file('image');
        $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/karir'), $filename);
        $karir->image = $filename;
      }

      if ($request->has('description')) {
        $karir->description = $validated['description'];
      }

      $karir->save();

      return response()->json([
        'success' => true,
        'data' => $karir,
        'message' => 'Karir berhasil diperbarui'
      ]);
    } catch (\Exception $e) {
      Log::error("Update failed: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'error' => 'Update failed'
      ], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $karir = Career::findOrFail($id);

      if ($karir->image) {
        $imagePath = public_path('images/karir/' . $karir->image);
        if (file_exists($imagePath)) {
          unlink($imagePath);
        }
      }

      $karir->delete();

      return response()->json([
        'success' => true,
        'message' => 'Karir berhasil dihapus'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Gagal menghapus karir',
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
