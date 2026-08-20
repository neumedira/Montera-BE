<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Ambil daftar semua notifikasi admin yang belum dibaca (is_read = false).
     */
    public function index(Request $request)
    {
        $notifications = DB::table('notifications')
            ->where('is_read', false)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $notifications
        ], 200);
    }

    /**
     * Tandai satu notifikasi spesifik sebagai sudah dibaca (is_read = true).
     */
    public function markAsRead(Request $request, $id)
    {
        $updated = DB::table('notifications')
            ->where('id', $id)
            ->update(['is_read' => true, 'updated_at' => now()]);

        if ($updated) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Notification marked as read'
            ], 200);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Notification not found'
        ], 404);
    }
}
