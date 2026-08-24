<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderAdminController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $status = $request->query('status');
            $paymentStatus = $request->query('payment_status');
            $orderType = $request->query('order_type');

            $orders = Order::with(['table', 'items'])
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                })
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($paymentStatus, function ($query, $paymentStatus) {
                    return $query->where('payment_status', $paymentStatus);
                })
                ->when($orderType, function ($query, $type) {
                    return $query->where('order_type', $type);
                })
                ->latest()
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($orders, 'Berhasil mengambil daftar pesanan');
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil daftar pesanan: ' . $e->getMessage(), null, 500);
        }
    }

    public function show($id)
    {
        try {
            $order = Order::with(['table', 'items'])->find($id);

            if (!$order) {
                return $this->errorResponse('Data pesanan tidak ditemukan', null, 404);
            }

            return $this->successResponse($order, 'Berhasil mengambil detail struk pesanan');
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil detail pesanan: ' . $e->getMessage(), null, 500);
        }
    }

   public function updateStatus(Request $request, $id)
{
    try {
        // Validasi disesuaikan ke payment_status
        $request->validate([
            'payment_status' => 'required|in:unpaid,paid',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return $this->errorResponse('Data pesanan tidak ditemukan', null, 404);
        }

        $order->payment_status = $request->payment_status;
        $order->save();

        return $this->successResponse($order, 'Berhasil memperbarui status pembayaran pesanan menjadi ' . $request->payment_status);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return $this->errorResponse('Validasi gagal', $e->errors(), 422);
    } catch (\Exception $e) {
        return $this->errorResponse('Gagal memperbarui status pesanan: ' . $e->getMessage(), null, 500);
    }
}
}
