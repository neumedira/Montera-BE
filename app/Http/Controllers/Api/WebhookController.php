<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleQrisWebhook(Request $request)
    {
        // 1. Simpan log payload masuk (untuk debugging/testing)
        Log::info('Webhook QRIS Received:', $request->all());

        $orderId = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');

        // 2. Cari data pesanan berdasarkan order_number
        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        // 3. Update status pembayaran berdasarkan callback
        if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
            $order->update(['payment_status' => 'paid']);

            // 4. Buat notifikasi baru di tabel notifications
            DB::table('notifications')->insert([
                'order_id'   => $order->id,
                'message'    => 'Pesanan baru masuk #' . $order->order_number,
                'is_read'    => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $order->update(['payment_status' => 'unpaid']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook processed successfully'
        ], 200);
    }
}
