<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller
{
  public function index()
  {
    $members = Member::all();

    return response()->json([
      'success' => true,
      'data' => $members
    ]);
  }

  public function show($id)
  {
    try {
      $member = Member::findOrFail($id);

      return response()->json([
        'success' => true,
        'data' => $member
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Member tidak ditemukan'
      ], 404);
    }
  }

  public function store(Request $request)
  {
    Log::info("Request store member:", $request->all());

    try {
      $validated = $request->validate([
        'member_code' => 'required|string|max:50|unique:members,member_code',
        'name'        => 'required|string|max:255',
        'address'     => 'required|string',
        'no_wa'       => 'required|string|max:20',
        'outlet'      => 'required|string|max:255'
      ]);

      $member = Member::create($validated);

      return response()->json([
        'success' => true,
        'message' => 'Member berhasil ditambahkan',
        'data'    => $member
      ], 201);
    } catch (\Exception $e) {
      Log::error("Error store member: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal menambahkan member'
      ], 500);
    }
  }

  public function update(Request $request, $id)
  {
    Log::info("Request update member:", $request->all());

    try {
      $member = Member::findOrFail($id);

      $validated = $request->validate([
        'member_code' => 'required|string|max:50|unique:members,member_code,' . $id,
        'name'        => 'required|string|max:255',
        'address'     => 'required|string',
        'no_wa'       => 'required|string|max:20',
        'outlet'      => 'required|string|max:255'
      ]);

      $member->update($validated);

      return response()->json([
        'success' => true,
        'message' => 'Member berhasil diperbarui',
        'data'    => $member
      ]);
    } catch (\Exception $e) {
      Log::error("Error update member: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal memperbarui member'
      ], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $member = Member::findOrFail($id);
      $member->delete();

      return response()->json([
        'success' => true,
        'message' => 'Member berhasil dihapus'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Gagal menghapus member'
      ], 500);
    }
  }
}
