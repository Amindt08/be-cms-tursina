<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
  public function index()
  {
    $menu = Menu::all();

    return response()->json([
      'success' => true,
      'data' => $menu
    ]);
  }

  public function show($id)
  {
    try {
      $menu = Menu::findOrFail($id);

      return response()->json([
        'success' => true,
        'data' => $menu
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Menu tidak ditemukan'
      ], 404);
    }
  }

  public function store(Request $request)
  {
    Log::info("Request store menu:", $request->all());

    try {
      $validated = $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'required|string'
      ]);

      $menu = new Menu();

      if ($request->hasFile('image')) {
        $image = $request->file('image');
        $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/menu'), $filename);
        $menu->image = $filename;
      }

      $menu->description = $validated['description'];
      $menu->save();

      return response()->json([
        'success' => true,
        'message' => 'Menu berhasil ditambahkan',
        'data' => $menu
      ], 201);
    } catch (\Exception $e) {
      Log::error("Error store menu: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal menambahkan menu'
      ], 500);
    }
  }

  public function update(Request $request, $id)
  {
    Log::info("Request update menu:", $request->all());

    try {
      $validated = $request->validate([
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'description' => 'required|string'
      ]);

      $menu = Menu::findOrFail($id);

      if ($request->hasFile('image')) {
        if ($menu->image && file_exists(public_path('images/menu/' . $menu->image))) {
          unlink(public_path('images/menu/' . $menu->image));
        }

        $image = $request->file('image');
        $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/menu'), $filename);
        $menu->image = $filename;
      }

      $menu->description = $validated['description'];
      $menu->save();

      return response()->json([
        'success' => true,
        'message' => 'Menu berhasil diperbarui',
        'data' => $menu
      ]);
    } catch (\Exception $e) {
      Log::error("Error update menu: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal memperbarui menu'
      ], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $menu = Menu::findOrFail($id);

      if ($menu->image && file_exists(public_path('images/menu/' . $menu->image))) {
        unlink(public_path('images/menu/' . $menu->image));
      }

      $menu->delete();

      return response()->json([
        'success' => true,
        'message' => 'Menu berhasil dihapus'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Gagal menghapus menu'
      ], 500);
    }
  }
}
