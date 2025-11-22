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
            // Validasi menggunakan field dari frontend
            $validated = $request->validate([
                'menu_name' => 'required|string',
                'details' => 'required|string',
                'price' => 'required|numeric',
                'category' => 'required|string',
                'status' => 'required|string',
                'image' => 'nullable' // bisa string (nama file)
            ]);

            $menu = new Menu();

            // Jika hanya mengirim nama file (string)
            if ($request->image && !$request->hasFile('image')) {
                $menu->image = $request->image;
            }

            // Jika upload file
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/menu'), $filename);
                $menu->image = $filename;
            }

            // Simpan data lain
            $menu->menu_name = $validated['menu_name'];
            $menu->details = $validated['details'];
            $menu->price = $validated['price'];
            $menu->category = $validated['category'];
            $menu->status = $validated['status'];

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
        $menu = Menu::findOrFail($id);

        $menu->menu_name = $request->menu_name;
        $menu->details = $request->details;
        $menu->price = $request->price;
        $menu->category = $request->category;
        $menu->status = $request->status;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/menu'), $filename);
            $menu->image = "/images/menu/" . $filename;
        }

        $menu->save();

        return response()->json(['success' => true, 'data' => $menu]);
    }


    public function destroy($id)
    {
        try {
            $menu = Menu::findOrFail($id);

            // Hapus file jika ada
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
