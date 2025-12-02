<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats()
    {
        try {
            $currentMonthStart = Carbon::now()->startOfMonth();
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            // Total counts - Tambah check table exists
            $totalWebVisits = DB::table('web_visits')->count();
            $totalMembers = DB::table('members')
                ->where('is_active', 1)
                ->count();
            $totalOutlets = DB::table('outlets')
                ->where('is_active', 1)
                ->count();
            $totalMenus = DB::table('menus')
                ->where('is_active', 1)
                ->count();

            // Current month visits
            $currentMonthVisits = DB::table('web_visits')
                ->where('created_at', '>=', $currentMonthStart)
                ->count();

            // Last month visits
            $lastMonthVisits = DB::table('web_visits')
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->count();

            // Current month members
            $currentMonthMembers = DB::table('members')
                ->where('created_at', '>=', $currentMonthStart)
                ->count();

            // Last month members
            $lastMonthMembers = DB::table('members')
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->count();

            // Calculate changes
            $webVisitsChange = $lastMonthVisits > 0 
                ? round((($currentMonthVisits - $lastMonthVisits) / $lastMonthVisits) * 100, 1)
                : ($currentMonthVisits > 0 ? 100 : 0);

            $membersChange = $lastMonthMembers > 0
                ? round((($currentMonthMembers - $lastMonthMembers) / $lastMonthMembers) * 100, 1)
                : ($currentMonthMembers > 0 ? 100 : 0);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_web_visits' => $totalWebVisits,
                    'total_members' => $totalMembers,
                    'total_outlets' => $totalOutlets,
                    'total_menus' => $totalMenus,
                    'web_visits_change' => $webVisitsChange,
                    'members_change' => $membersChange,
                ]
            ]);

        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Dashboard stats error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data statistik',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getTopMembers(Request $request)
    {
        try {
            $limit = $request->get('limit', 5);

            // FIX: Gunakan 'outlet_id' dan 'points' sesuai struktur tabel
            $members = DB::table('members')
                ->select(
                    'members.id',
                    'members.name',
                    'members.points', // Bukan total_points
                    'outlets.location as outlet'
                )
                ->leftJoin('outlets', 'members.outlet_id', '=', 'outlets.id') // Bukan home_outlet_id
                ->where('members.is_active', 1)
                ->orderBy('members.points', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $members
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard top members error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data member',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getDailyVisits()
    {
        try {
            $visits = DB::table('web_visits')
                ->select(
                    DB::raw('DATE(visited_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('visited_at', '>=', Carbon::now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $visits
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard daily visits error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kunjungan',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}