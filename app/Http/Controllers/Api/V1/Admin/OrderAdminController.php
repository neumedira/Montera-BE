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

    // =========================================================
    // LIST ORDERS
    // GET /api/v1/admin/orders
    // =========================================================

    public function index(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $status = $request->query('status');
            $paymentStatus = $request->query('payment_status');
            $orderType = $request->query('order_type');

            $orders = Order::with([
                'table',
                'items.menuItem',
                'items.addons',
            ])
                ->when(
                    $startDate && $endDate,
                    function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('created_at', [
                            Carbon::parse($startDate)->startOfDay(),
                            Carbon::parse($endDate)->endOfDay(),
                        ]);
                    }
                )

                // =================================================
                // FILTER STATUS
                // =================================================

                ->when(
                    $status,
                    function ($query, $status) {
                        return $query->where('status', $status);
                    },
                    function ($query) {
                        // Default:
                        // Jangan tampilkan order yang sudah done
                        return $query->where('status', '!=', 'done');
                    }
                )

                // =================================================
                // FILTER PAYMENT STATUS
                // =================================================

                ->when(
                    $paymentStatus,
                    function ($query, $paymentStatus) {
                        return $query->where(
                            'payment_status',
                            $paymentStatus
                        );
                    }
                )

                // =================================================
                // FILTER ORDER TYPE
                // =================================================

                ->when(
                    $orderType,
                    function ($query, $type) {
                        return $query->where(
                            'order_type',
                            $type
                        );
                    }
                )

                ->latest()
                ->paginate(
                    $request->get('per_page', 15)
                );

            return $this->successResponse(
                $orders,
                'Berhasil mengambil daftar pesanan'
            );

        } catch (\Exception $e) {

            return $this->errorResponse(
                'Gagal mengambil daftar pesanan: ' .
                    $e->getMessage(),
                null,
                500
            );
        }
    }

    // =========================================================
    // DETAIL ORDER
    // GET /api/v1/admin/orders/{id}
    // =========================================================

    public function show($id)
    {
        try {
            $order = Order::with([
                'table',
                'items.menuItem',
                'items.addons',
            ])->find($id);

            if (!$order) {
                return $this->errorResponse(
                    'Data pesanan tidak ditemukan',
                    null,
                    404
                );
            }

            return $this->successResponse(
                $order,
                'Berhasil mengambil detail struk pesanan'
            );

        } catch (\Exception $e) {

            return $this->errorResponse(
                'Gagal mengambil detail pesanan: ' .
                    $e->getMessage(),
                null,
                500
            );
        }
    }

    // =========================================================
    // UPDATE ORDER STATUS
    // PATCH /api/v1/admin/orders/{id}/status
    // =========================================================

    public function updateStatus(Request $request, $id)
    {
        try {

            // =====================================================
            // VALIDASI
            // Bisa update:
            // - status
            // - payment_status
            // =====================================================

            $validated = $request->validate([
                'status' => [
                    'sometimes',
                    'in:pending,processing,ready,done',
                ],

                'payment_status' => [
                    'sometimes',
                    'in:unpaid,paid',
                ],
            ]);

            // =====================================================
            // CARI ORDER
            // =====================================================

            $order = Order::find($id);

            if (!$order) {
                return $this->errorResponse(
                    'Data pesanan tidak ditemukan',
                    null,
                    404
                );
            }

            // =====================================================
            // CEK PERUBAHAN
            // =====================================================

            $statusChanged = false;
            $paymentStatusChanged = false;

            // =====================================================
            // UPDATE STATUS ORDER
            // =====================================================

            if ($request->has('status')) {

                if ($order->status !== $request->status) {
                    $statusChanged = true;
                }

                $order->status =
                    $request->status;
            }

            // =====================================================
            // UPDATE PAYMENT STATUS
            // =====================================================

            if ($request->has('payment_status')) {

                if (
                    $order->payment_status !==
                    $request->payment_status
                ) {
                    $paymentStatusChanged = true;
                }

                $order->payment_status =
                    $request->payment_status;
            }

            // =====================================================
            // SIMPAN
            // =====================================================

            $order->save();

            // =====================================================
            // NOTIFICATION
            // =====================================================

            $notificationMessage = null;
            $notificationTitle = null;

            // -----------------------------------------------------
            // PAYMENT STATUS CHANGED
            // -----------------------------------------------------

            if ($paymentStatusChanged) {

                $notificationTitle =
                    'Status Pembayaran Diperbarui';

                $notificationMessage =
                    "Pesanan #{$order->order_number} " .
                    "status pembayarannya menjadi " .
                    "{$order->payment_status}.";
            }

            // -----------------------------------------------------
            // ORDER STATUS CHANGED
            // -----------------------------------------------------

            if ($statusChanged) {

                $notificationTitle =
                    'Status Pesanan Diperbarui';

                $notificationMessage =
                    "Pesanan #{$order->order_number} " .
                    "status pesanan menjadi " .
                    "{$order->status}.";
            }

            // -----------------------------------------------------
            // INSERT NOTIFICATION
            // -----------------------------------------------------

            if (
                $notificationMessage &&
                $notificationTitle
            ) {

                $notifId =
                    DB::table('notifications')
                        ->insertGetId([
                            'order_id' =>
                                $order->id,

                            'message' =>
                                $notificationMessage,

                            'is_read' =>
                                false,

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);

                $notification =
                    DB::table('notifications')
                        ->where(
                            'id',
                            $notifId
                        )
                        ->first();

                // Tambahan untuk WebSocket
                $notification->title =
                    $notificationTitle;

                $notification->message =
                    $notificationMessage;

                event(
                    new NewNotificationEvent(
                        $notification
                    )
                );
            }

            // =====================================================
            // RESPONSE
            // =====================================================

            $message = 'Order berhasil diperbarui.';

            if ($statusChanged) {
                $message =
                    'Status pesanan berhasil diperbarui menjadi ' .
                    $order->status . '.';
            }

            if ($paymentStatusChanged) {
                $message =
                    'Status pembayaran berhasil diperbarui menjadi ' .
                    $order->payment_status . '.';
            }

            if (
                $statusChanged &&
                $paymentStatusChanged
            ) {
                $message =
                    'Status pesanan dan pembayaran berhasil diperbarui.';
            }

            return $this->successResponse(
                $order,
                $message
            );

        } catch (
            \Illuminate\Validation\ValidationException $e
        ) {

            return $this->errorResponse(
                'Validasi gagal',
                $e->errors(),
                422
            );

        } catch (\Exception $e) {

            return $this->errorResponse(
                'Gagal memperbarui status pesanan: ' .
                    $e->getMessage(),
                null,
                500
            );
        }
    }
}

