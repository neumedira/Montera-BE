<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Ambil semua notifikasi yang belum dibaca.
     */
    public function index(Request $request)
    {
        $notifications =
            DB::table('notifications')
                ->where(
                    'is_read',
                    false
                )
                ->latest()
                ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil notifikasi.',
            'data' => $notifications,
        ], 200);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(
        Request $request,
        $id
    ) {
        $updated =
            DB::table('notifications')
                ->where(
                    'id',
                    $id
                )
                ->update([
                    'is_read' => true,
                    'updated_at' => now(),
                ]);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sudah dibaca.',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notifikasi tidak ditemukan.',
        ], 404);
    }
}
