<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
	public function index()
	{
		$gallery = Gallery::all();

		return response()->json([
			'success' => true,
			'data' => $gallery
		]);
	}

	public function show($id)
	{
		try {
			$gallery = Gallery::findOrFail($id);

			return response()->json([
				'success' => true,
				'data' => $gallery
			]);
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'error' => 'Data galeri tidak ditemukan'
			], 404);
		}
	}

	public function store(Request $request)
	{
		Log::info("Request store gallery:", $request->all());

		try {
			$validated = $request->validate([
				'category' => 'required|string|max:255',
				'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
				'description' => 'nullable|string'
			]);

			$gallery = new Gallery();

			// Upload image
			if ($request->hasFile('image')) {
				$image = $request->file('image');
				$filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
				$image->move(public_path('images/gallery'), $filename);
				$gallery->image = $filename;
			}

			$gallery->category = $validated['category'];
			$gallery->description = $validated['description'] ?? null;
			$gallery->save();

			return response()->json([
				'success' => true,
				'message' => 'Galeri berhasil ditambahkan',
				'data' => $gallery
			], 201);
		} catch (\Exception $e) {
			Log::error("Error store gallery: " . $e->getMessage());

			return response()->json([
				'success' => false,
				'error' => 'Gagal menambahkan galeri'
			], 500);
		}
	}

	public function update(Request $request, $id)
	{
		Log::info("Request update gallery:", $request->all());

		try {
			$validated = $request->validate([
				'category' => 'required|string|max:255',
				'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
				'description' => 'nullable|string'
			]);

			$gallery = Gallery::findOrFail($id);

			// Jika ada gambar baru
			if ($request->hasFile('image')) {

				// Hapus gambar lama
				if ($gallery->image && file_exists(public_path('images/gallery/' . $gallery->image))) {
					unlink(public_path('images/gallery/' . $gallery->image));
				}

				$image = $request->file('image');
				$filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
				$image->move(public_path('images/gallery'), $filename);
				$gallery->image = $filename;
			}

			$gallery->category = $validated['category'];
			$gallery->description = $validated['description'] ?? null;
			$gallery->save();

			return response()->json([
				'success' => true,
				'message' => 'Galeri berhasil diperbarui',
				'data' => $gallery
			]);
		} catch (\Exception $e) {
			Log::error("Error update gallery: " . $e->getMessage());

			return response()->json([
				'success' => false,
				'error' => 'Gagal memperbarui galeri'
			], 500);
		}
	}

	public function destroy($id)
	{
		try {
			$gallery = Gallery::findOrFail($id);

			// Hapus file gambar
			if ($gallery->image && file_exists(public_path('images/gallery/' . $gallery->image))) {
				unlink(public_path('images/gallery/' . $gallery->image));
			}

			$gallery->delete();

			return response()->json([
				'success' => true,
				'message' => 'Galeri berhasil dihapus'
			]);
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'error' => 'Gagal menghapus galeri'
			], 500);
		}
	}
}
