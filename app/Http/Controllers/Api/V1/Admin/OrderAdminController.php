<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Events\NewNotificationEvent;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            // LOGIKA FILTER STATUS:
            ->when($status, function ($query, $status) {
                // Jika frontend mengirim parameter status (misal: status=done, status=pending, dll)
                return $query->where('status', $status);
            }, function ($query) {
                // JIKA TAB "SEMUA" DIPIIHIH (status kosong):
                // Sembunyikan pesanan yang sudah 'done' dari tab Semua
                return $query->where('status', '!=', 'done');
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
            $request->validate([
                'payment_status' => 'required|in:unpaid,paid',
            ]);

            $order = Order::find($id);

            if (!$order) {
                return $this->errorResponse('Data pesanan tidak ditemukan', null, 404);
            }

            $order->payment_status = $request->payment_status;
            $order->save();

            // 1. Insert ke tabel notifications (termasuk order_id)
            $notifId = DB::table('notifications')->insertGetId([
    'order_id'   => $order->id,
    'message'    => "Pesanan #{$order->id} telah diperbarui status pembayarannya menjadi {$request->payment_status}.",
    'is_read'    => false,
    'created_at' => now(),
    'updated_at' => now(),
]);

            // 2. Ambil data notifikasi yang baru diinsert
            $notification = DB::table('notifications')->where('id', $notifId)->first();

            // 3. Tambahkan info tambahan secara dinamis untuk dikirim via WebSocket ke Frontend
            $notification->title = 'Status Pembayaran Diperbarui';
            $notification->message = "Pesanan #{$order->id} telah diperbarui status pembayarannya menjadi {$request->payment_status}.";

            // 4. Trigger Broadcast WebSocket ke Reverb!
            event(new NewNotificationEvent($notification));

            return $this->successResponse($order, 'Berhasil memperbarui status pembayaran pesanan menjadi ' . $request->payment_status);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validasi gagal', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memperbarui status pesanan: ' . $e->getMessage(), null, 500);
        }
    }
}
