<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

  public function getOutlets()
  {
    try {
      $outlets = Outlet::all();

      return response()->json([
        'success' => true,
        'data' => $outlets
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Gagal mengambil data outlet'
      ], 500);
    }
  }

  private function generateMemberCode($outletLocation)
  {
    // Ambil huruf depan outlet location (uppercase)
    $outletInitial = strtoupper(substr($outletLocation, 0, 3));

    // Cari member terakhir dengan outlet yang sama
    $lastMember = Member::where('member_code', 'like', $outletInitial . '-%')
      ->orderBy('member_code', 'desc')
      ->first();

    if ($lastMember) {
      // Extract number and increment
      $lastNumber = intval(substr($lastMember->member_code, 4));
      $newNumber = $lastNumber + 1;
    } else {
      $newNumber = 1;
    }

    // Format: OUT-001
    return $outletInitial . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
  }

  public function store(Request $request)
  {
    Log::info("Request store member:", $request->all());

    try {
      $validated = $request->validate([
        'name'        => 'required|string|max:255',
        'address'     => 'required|string',
        'no_wa'       => 'required|string|max:20',
        'outlet_id'   => 'required|exists:outlets,id',
        'points'      => 'sometimes|integer|min:0',
        'total_points_earned' => 'sometimes|integer|min:0',
        'total_points_redeemed' => 'sometimes|integer|min:0',
        'is_active'  => 'sometimes'
      ]);
      $validated['is_active'] = $this->convertToInteger($validated['is_active']);

      DB::beginTransaction();

      // Get outlet data
      $outlet = Outlet::findOrFail($validated['outlet_id']);

      // Generate member_code menggunakan location
      $memberCode = $this->generateMemberCode($outlet->location);

      // Tambahkan member_code dan outlet name ke validated data
      $validated['member_code'] = $memberCode;
      $validated['outlet'] = $outlet->location; // Isi kolom outlet dengan nama outlet

      // Set default values untuk points jika tidak disediakan
      $validated['points'] = $validated['points'] ?? 0;
      $validated['total_points_earned'] = $validated['total_points_earned'] ?? 0;
      $validated['total_points_redeemed'] = $validated['total_points_redeemed'] ?? 0;

      $member = Member::create($validated);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Member berhasil ditambahkan',
        'data'    => $member
      ], 201);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Error store member: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal menambahkan member: ' . $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, $id)
  {
    Log::info("Request update member:", $request->all());

    try {
      $member = Member::findOrFail($id);

      $validated = $request->validate([
        'name'        => 'required|string|max:255',
        'address'     => 'required|string',
        'no_wa'       => 'required|string|max:20',
        'outlet_id'   => 'required|exists:outlets,id',
        'points'      => 'sometimes|integer|min:0',
        'total_points_earned' => 'sometimes|integer|min:0',
        'total_points_redeemed' => 'sometimes|integer|min:0',
        'is_active'  => 'sometimes'
      ]);
      $validated['is_active'] = $this->convertToInteger($validated['is_active']);

      DB::beginTransaction();

      // Get outlet data
      $outlet = Outlet::findOrFail($validated['outlet_id']);

      // Generate member_code baru jika outlet berubah
      if ($member->outlet_id != $validated['outlet_id']) {
        $memberCode = $this->generateMemberCode($outlet->location);
        $validated['member_code'] = $memberCode;
      }

      $validated['outlet'] = $outlet->location; // Update outlet name

      $member->update($validated);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Member berhasil diperbarui',
        'data'    => $member
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Error update member: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal memperbarui member: ' . $e->getMessage()
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

  // ===== METHODS BARU UNTUK MANAGEMEN POINTS =====

  /**
   * Menambah points member (saat pembelian)
   */
  public function addPoints(Request $request, $id)
  {
    Log::info("Request add points:", $request->all());

    try {
      $member = Member::findOrFail($id);

      $validated = $request->validate([
        'points' => 'required|integer|min:1',
        'transaction_id' => 'sometimes|string', // ID transaksi pembelian (optional)
        'notes' => 'sometimes|string' // Catatan tambahan
      ]);

      DB::beginTransaction();

      $pointsToAdd = $validated['points'];

      // Update points member
      $member->points += $pointsToAdd;
      $member->total_points_earned += $pointsToAdd;
      $member->save();

      // Log the points addition (optional - bisa dibuat table points_history)
      Log::info("Points added to member {$member->id}: {$pointsToAdd} points");

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => "{$pointsToAdd} points berhasil ditambahkan ke member {$member->name}",
        'data' => [
          'member' => $member,
          'points_added' => $pointsToAdd,
          'new_balance' => $member->points
        ]
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Error adding points: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal menambah points: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Mengurangi points member (saat penukaran/penggunaan)
   */
  public function redeemPoints(Request $request, $id)
  {
    Log::info("Request redeem points:", $request->all());

    try {
      $member = Member::findOrFail($id);

      $validated = $request->validate([
        'points' => 'required|integer|min:1',
        'discount_amount' => 'sometimes|numeric|min:0', // Jumlah diskon yang diberikan
        'transaction_id' => 'sometimes|string', // ID transaksi penukaran
        'notes' => 'sometimes|string'
      ]);

      $pointsToRedeem = $validated['points'];

      // Validasi apakah points mencukupi
      if ($member->points < $pointsToRedeem) {
        return response()->json([
          'success' => false,
          'error' => 'Points tidak mencukupi. Points tersedia: ' . $member->points
        ], 400);
      }

      DB::beginTransaction();

      // Update points member
      $member->points -= $pointsToRedeem;
      $member->total_points_redeemed += $pointsToRedeem;
      $member->save();

      // Log the points redemption
      Log::info("Points redeemed from member {$member->id}: {$pointsToRedeem} points");

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => "{$pointsToRedeem} points berhasil ditukarkan oleh member {$member->name}",
        'data' => [
          'member' => $member,
          'points_redeemed' => $pointsToRedeem,
          'new_balance' => $member->points,
          'discount_given' => $validated['discount_amount'] ?? null
        ]
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Error redeeming points: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal menukar points: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Reset points member (opsional - untuk admin)
   */
  public function resetPoints($id)
  {
    try {
      $member = Member::findOrFail($id);

      DB::beginTransaction();

      $oldPoints = $member->points;

      $member->points = 0;
      $member->save();

      Log::info("Points reset for member {$member->id}. Old points: {$oldPoints}");

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Points berhasil direset ke 0',
        'data' => $member
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Error resetting points: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'error' => 'Gagal mereset points: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get points history (jika ingin mengimplementasi history)
   */
  public function getPointsHistory($id)
  {
    try {
      $member = Member::findOrFail($id);

      // Untuk sementara return summary, bisa dikembangkan dengan table terpisah
      return response()->json([
        'success' => true,
        'data' => [
          'member' => $member,
          'points_summary' => [
            'current_points' => $member->points,
            'total_earned' => $member->total_points_earned,
            'total_redeemed' => $member->total_points_redeemed,
            'lifetime_points' => $member->total_points_earned - $member->total_points_redeemed
          ]
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'error' => 'Gagal mengambil history points'
      ], 500);
    }
  }

  private function convertToInteger($value): int
  {
    if (is_bool($value)) {
      return $value ? 1 : 0;
    }

    if (is_string($value)) {
      // Handle '1', '0', 'true', 'false'
      return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    if (is_numeric($value)) {
      return (int) $value;
    }

    return 0; // Default
  }
}
