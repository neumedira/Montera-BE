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

    public function index(Request $request)
    {
        try {
            $today = Carbon::today();
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $orderQuery = Order::query();

            // Filter rentang tanggal jika opsi start_date dan end_date dikirim
            if ($startDate && $endDate) {
                $orderQuery->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            $totalOrders = (clone $orderQuery)->count();
            $totalRevenue = (clone $orderQuery)->where('payment_status', 'paid')->sum('total_amount');

            $todayOrders = Order::whereDate('created_at', $today)->count();
            $todayRevenue = Order::whereDate('created_at', $today)
                                 ->where('payment_status', 'paid')
                                 ->sum('total_amount');

            $data = [
                'total_orders'  => $totalOrders,
                'total_revenue' => (float) $totalRevenue,
                'today_orders'  => $todayOrders,
                'today_revenue' => (float) $todayRevenue,
                'filter'        => [
                    'start_date' => $startDate ?? null,
                    'end_date'   => $endDate ?? null,
                ]
            ];

            return $this->successResponse($data, 'Berhasil mengambil data statistik dashboard admin');

        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil data dashboard: ' . $e->getMessage(), null, 500);
        }
    }
}
