<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Traits\ApiResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Menampilkan data statistik untuk Dashboard Admin
     */
    public function index()
    {
        try {
            // Tanggal hari ini
            $today = Carbon::today();

            // 1. Total Order (Keseluruhan)
            $totalOrderAllTime = Order::count();

            // 2. Total Order (Hari Ini)
            $totalOrderToday = Order::whereDate('created_at', $today)->count();

            // 3. Pendapatan Hari Ini (Pesanan hari ini dengan payment_status 'paid')
            $revenueToday = Order::whereDate('created_at', $today)
                                 ->where('payment_status', 'paid')
                                 ->sum('total_amount');

            $data = [
                'total_order_all_time' => $totalOrderAllTime,
                'total_order_today'    => $totalOrderToday,
                'revenue_today'        => (float) $revenueToday,
            ];

            return $this->successResponse($data, 'Berhasil mengambil data statistik dashboard admin');

        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil data dashboard: ' . $e->getMessage());
        }
    }
}
