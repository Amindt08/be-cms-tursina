<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Menu::all()
        ]);
    }

    public function show($id)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => Menu::findOrFail($id)
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
            $validator = Validator::make($request->all(), [
                'menu_name' => 'required|string|max:255',
                'details'   => 'required|string',
                'price'     => 'required|numeric|min:0',
                'category'  => 'required|string|max:100',
                'status'    => 'required|string|in:active,inactive',
                'image'     => 'nullable|file|image|max:2048'
            ]);

            if ($validator->fails()) {
                Log::error("Validation failed:", $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $menu = new Menu();
            $menu->menu_name = $request->menu_name;
            $menu->details   = $request->details;
            $menu->price     = $request->price;
            $menu->category  = $request->category;
            $menu->status    = $request->status;

            // Upload image (save filename only)
            if ($request->hasFile('image')) {
                $file      = $request->file('image');
                $filename  = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/menu'), $filename);
                $menu->image = $filename;
            }

            $menu->save();

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil ditambahkan',
                'data'    => $menu
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error store menu: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan menu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info("=== UPDATE MENU REQUEST ===");
            Log::info("Menu ID: " . $id);
            Log::info("Method: " . $request->method());
            Log::info("CT: " . $request->header('Content-Type'));

            // Parse input
            $menuName = $request->input('menu_name');
            $details  = $request->input('details');
            $price    = $request->input('price');
            $category = $request->input('category');
            $status   = $request->input('status');

            Log::info("Parsed data:", compact('menuName', 'details', 'price', 'category', 'status'));

            $menu = Menu::findOrFail($id);

            // Validasi
            $validator = Validator::make([
                'menu_name' => $menuName,
                'details'   => $details,
                'price'     => $price,
                'category'  => $category,
                'status'    => $status,
            ], [
                'menu_name' => 'required|string|max:255',
                'details'   => 'required|string',
                'price'     => 'required|numeric|min:0',
                'category'  => 'required|string|max:100',
                'status'    => 'required|string|in:active,inactive',
            ]);

            if ($validator->fails()) {
                Log::error("Validation failed:", $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // Update data
            $menu->menu_name = $menuName;
            $menu->details   = $details;
            $menu->price     = $price;
            $menu->category  = $category;
            $menu->status    = $status;

            if ($request->hasFile('image')) {

                Log::info("Uploading new image file");

                // Delete old file
                if ($menu->image) {
                    $oldPath = public_path('images/menu/' . $menu->image);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                        Log::info("Old image deleted: " . $oldPath);
                    }
                }

                // Upload new
                $file     = $request->file('image');
                $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/menu'), $filename);

                $menu->image = $filename;
                Log::info("New image saved: $filename");
            } elseif ($request->filled('old_image')) {
                $menu->image = basename($request->old_image);
            }

            $menu->save();

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil diupdate',
                'data'    => $menu
            ]);
        } catch (\Exception $e) {
            Log::error("Error update menu: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate menu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $menu = Menu::findOrFail($id);

            if ($menu->image) {
                $imagePath = public_path('images/menu/' . $menu->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $menu->delete();

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error("Error delete menu: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal menghapus menu'
            ], 500);
        }
    }
}
