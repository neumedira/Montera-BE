<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource (Daftar Pesanan Masuk).
     */
    public function index(Request $request)
    {
        try {
            // Mengambil daftar pesanan beserta relasi meja dan item
            // Mendukung filter query string opsional: ?payment_status=paid atau ?order_type=dine-in
            $orders = Order::with(['table', 'items'])
                ->when($request->payment_status, function ($query, $status) {
                    return $query->where('payment_status', $status);
                })
                ->when($request->order_type, function ($query, $type) {
                    return $query->where('order_type', $type);
                })
                ->latest()
                ->paginate($request->get('per_page', 15));

            return $this->successResponse($orders, 'Berhasil mengambil daftar pesanan');
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil daftar pesanan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource (Detail Struk Pesanan).
     */
    public function show($id)
    {
        try {
            // Mencari order berdasarkan ID beserta relasi meja dan item detailnya
            $order = Order::with(['table', 'items'])->find($id);

            if (!$order) {
                return $this->errorResponse('Data pesanan tidak ditemukan', null, 404);
            }

            return $this->successResponse($order, 'Berhasil mengambil detail struk pesanan');
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil detail pesanan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Verifikasi Pembayaran Manual QRIS oleh Admin (Revisi Client).
     */
    public function verifyPayment(Request $request, $id)
    {
        try {
            $request->validate([
                'payment_status' => 'required|in:paid,failed',
            ]);

            $order = Order::find($id);

            if (!$order) {
                return $this->errorResponse('Data pesanan tidak ditemukan', null, 404);
            }

            $order->update([
                'payment_status' => $request->payment_status,
            ]);

            return $this->successResponse($order, 'Status pembayaran berhasil diperbarui secara manual');
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memperbarui status pembayaran: ' . $e->getMessage(), null, 500);
        }
    }
}
