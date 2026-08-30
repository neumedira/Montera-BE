<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Bundle;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            // =====================================================
            // TIMEZONE
            // =====================================================
            //
            // Database menyimpan created_at dalam UTC.
            // Aplikasi menggunakan waktu Indonesia Tengah.
            //
            // =====================================================

            $timezone = 'Asia/Makassar';

            $todayLocal =
                Carbon::now($timezone)->startOfDay();

            $tomorrowLocal =
                $todayLocal
                    ->copy()
                    ->addDay();

            // Konversi batas lokal -> UTC
            $todayStartUtc =
                $todayLocal
                    ->copy()
                    ->setTimezone('UTC');

            $tomorrowStartUtc =
                $tomorrowLocal
                    ->copy()
                    ->setTimezone('UTC');

            // =====================================================
            // REQUEST FILTER
            // =====================================================

            $period =
                $request->query('period');

            $startDate =
                $request->query('start_date');

            $endDate =
                $request->query('end_date');

            // =====================================================
            // MAIN REPORT QUERY
            // =====================================================

            $reportOrderQuery =
                Order::query();

            // =====================================================
            // SEMUA
            // =====================================================

            if (
                $period === 'all'
            ) {

                // Tidak ada filter tanggal.

            }

            // =====================================================
            // HARI INI
            // =====================================================

            elseif (
                $period === 'today'
            ) {

                $reportOrderQuery
                    ->where(
                        'created_at',
                        '>=',
                        $todayStartUtc
                    )
                    ->where(
                        'created_at',
                        '<',
                        $tomorrowStartUtc
                    );

            }

            // =====================================================
            // 7 HARI
            // =====================================================

            elseif (
                $period === '7_days'
            ) {

                $startLocal =
                    $todayLocal
                        ->copy()
                        ->subDays(6)
                        ->startOfDay();

                $endLocal =
                    $tomorrowLocal
                        ->copy()
                        ->startOfDay();

                $startUtc =
                    $startLocal
                        ->copy()
                        ->setTimezone('UTC');

                $endUtc =
                    $endLocal
                        ->copy()
                        ->setTimezone('UTC');

                $reportOrderQuery
                    ->where(
                        'created_at',
                        '>=',
                        $startUtc
                    )
                    ->where(
                        'created_at',
                        '<',
                        $endUtc
                    );

            }

            // =====================================================
            // 30 HARI
            // =====================================================

            elseif (
                $period === '30_days'
            ) {

                $startLocal =
                    $todayLocal
                        ->copy()
                        ->subDays(29)
                        ->startOfDay();

                $endLocal =
                    $tomorrowLocal
                        ->copy()
                        ->startOfDay();

                $startUtc =
                    $startLocal
                        ->copy()
                        ->setTimezone('UTC');

                $endUtc =
                    $endLocal
                        ->copy()
                        ->setTimezone('UTC');

                $reportOrderQuery
                    ->where(
                        'created_at',
                        '>=',
                        $startUtc
                    )
                    ->where(
                        'created_at',
                        '<',
                        $endUtc
                    );

            }

            // =====================================================
            // CUSTOM DATE RANGE
            // =====================================================

            elseif (
                $startDate &&
                $endDate
            ) {

                $startLocal =
                    Carbon::createFromFormat(
                        'Y-m-d',
                        $startDate,
                        $timezone
                    )->startOfDay();

                $endLocal =
                    Carbon::createFromFormat(
                        'Y-m-d',
                        $endDate,
                        $timezone
                    )
                    ->addDay()
                    ->startOfDay();

                $startUtc =
                    $startLocal
                        ->copy()
                        ->setTimezone('UTC');

                $endUtc =
                    $endLocal
                        ->copy()
                        ->setTimezone('UTC');

                $reportOrderQuery
                    ->where(
                        'created_at',
                        '>=',
                        $startUtc
                    )
                    ->where(
                        'created_at',
                        '<',
                        $endUtc
                    );

            }

            // =====================================================
            // CUSTOM SINGLE DATE
            // =====================================================

            elseif (
                $startDate
            ) {

                $startLocal =
                    Carbon::createFromFormat(
                        'Y-m-d',
                        $startDate,
                        $timezone
                    )->startOfDay();

                $endLocal =
                    $startLocal
                        ->copy()
                        ->addDay();

                $startUtc =
                    $startLocal
                        ->copy()
                        ->setTimezone('UTC');

                $endUtc =
                    $endLocal
                        ->copy()
                        ->setTimezone('UTC');

                $reportOrderQuery
                    ->where(
                        'created_at',
                        '>=',
                        $startUtc
                    )
                    ->where(
                        'created_at',
                        '<',
                        $endUtc
                    );

            }

            // =====================================================
            // CUSTOM END DATE ONLY
            // =====================================================

            elseif (
                $endDate
            ) {

                $endLocal =
                    Carbon::createFromFormat(
                        'Y-m-d',
                        $endDate,
                        $timezone
                    )
                    ->addDay()
                    ->startOfDay();

                $endUtc =
                    $endLocal
                        ->copy()
                        ->setTimezone('UTC');

                $reportOrderQuery
                    ->where(
                        'created_at',
                        '<',
                        $endUtc
                    );

            }

            // =====================================================
            // DEFAULT
            // =====================================================
            //
            // Dashboard tanpa parameter = Hari Ini
            //
            // =====================================================

            else {

                $reportOrderQuery
                    ->where(
                        'created_at',
                        '>=',
                        $todayStartUtc
                    )
                    ->where(
                        'created_at',
                        '<',
                        $tomorrowStartUtc
                    );
            }

            // =====================================================
            // SUMMARY
            // =====================================================

            $totalOrders =
                (clone $reportOrderQuery)
                    ->count();

            $totalRevenue =
                (clone $reportOrderQuery)
                    ->sum(
                        'total_amount'
                    );

            // =====================================================
            // ACTIVE MENU + BUNDLE
            // =====================================================

            $activeMenuCount =
                MenuItem::where(
                    'is_active',
                    true
                )->count();

            $activeBundleCount =
                Bundle::where(
                    'is_active',
                    true
                )->count();

            $activeProductCount =
                $activeMenuCount +
                $activeBundleCount;

            // =====================================================
            // PAYMENT METHOD BREAKDOWN
            // =====================================================

            $paymentBreakdown =
                (clone $reportOrderQuery)
                    ->select(
                        'payment_method',
                        DB::raw(
                            'COUNT(*) as order_count'
                        ),
                        DB::raw(
                            'SUM(total_amount) as total_amount'
                        )
                    )
                    ->groupBy(
                        'payment_method'
                    )
                    ->get()
                    ->groupBy(
                        function ($item) {

                            $method =
                                strtolower(
                                    trim(
                                        $item->payment_method ??
                                        ''
                                    )
                                );

                            if (
                                $method === 'cash' ||
                                $method === 'tunai'
                            ) {
                                return 'cash';
                            }

                            if (
                                $method === 'qris' ||
                                str_starts_with(
                                    $method,
                                    'qris_'
                                )
                            ) {
                                return 'qris';
                            }

                            if (
                                $method === 'tf_bank' ||
                                str_starts_with(
                                    $method,
                                    'tf_bank_'
                                )
                            ) {
                                return 'tf_bank';
                            }

                            if (
                                $method === 'ewallet' ||
                                str_starts_with(
                                    $method,
                                    'ewallet_'
                                )
                            ) {
                                return 'ewallet';
                            }

                            if (
                                $method === 'kartu' ||
                                str_starts_with(
                                    $method,
                                    'kartu_'
                                )
                            ) {
                                return 'kartu';
                            }

                            return $method !== ''
                                ? $method
                                : 'other';
                        }
                    )
                    ->map(
                        function (
                            $items,
                            $normalizedMethod
                        ) {

                            return [
                                'payment_method' =>
                                    $normalizedMethod,

                                'order_count' =>
                                    (int)
                                    $items->sum(
                                        'order_count'
                                    ),

                                'total_amount' =>
                                    (float)
                                    $items->sum(
                                        'total_amount'
                                    ),
                            ];
                        }
                    )
                    ->sortByDesc(
                        'total_amount'
                    )
                    ->values();

            // =====================================================
            // SOLD ITEMS
            // =====================================================

            $reportOrderItems =
                (clone $reportOrderQuery)
                    ->with([
                        'items.menuItem',
                        'items.bundle',
                    ])
                    ->get()
                    ->flatMap(
                        function ($order) {
                            return $order->items;
                        }
                    );

            // =====================================================
            // GROUP SOLD ITEMS
            // =====================================================

            $soldItemGroups = [];

            foreach (
                $reportOrderItems as $item
            ) {

                // =================================================
                // BUNDLE
                // =================================================

                if (
                    $item->bundle_id
                ) {

                    $bundleId =
                        (int)
                        $item->bundle_id;

                    $key =
                        'bundle-' .
                        $bundleId;

                    if (
                        !isset(
                            $soldItemGroups[$key]
                        )
                    ) {

                        $soldItemGroups[$key] = [
                            'item_type' =>
                                'bundle',

                            'bundle_id' =>
                                $bundleId,

                            'menu_item_id' =>
                                null,

                            'name' =>
                                $item
                                    ->bundle
                                    ?->name ??
                                'Bundle',

                            'quantity' =>
                                (int)
                                $item->quantity,

                            'price' =>
                                (float) (
                                    $item
                                        ->bundle
                                        ?->bundle_price ??
                                    $item
                                        ->bundle
                                        ?->price ??
                                    $item
                                        ->unit_price ??
                                    0
                                ),

                            'total' =>
                                0,
                        ];

                    } else {

                        $soldItemGroups[$key]['quantity'] =
                            min(
                                $soldItemGroups[$key]['quantity'],
                                (int)
                                $item->quantity
                            );
                    }

                    continue;
                }

                // =================================================
                // MENU BIASA
                // =================================================

                $menuItemId =
                    (int)
                    $item->menu_item_id;

                $key =
                    'menu-' .
                    $menuItemId;

                if (
                    !isset(
                        $soldItemGroups[$key]
                    )
                ) {

                    $soldItemGroups[$key] = [
                        'item_type' =>
                            'menu',

                        'bundle_id' =>
                            null,

                        'menu_item_id' =>
                            $menuItemId,

                        'name' =>
                            $item
                                ->menuItem
                                ?->name ??
                            $item->item_name ??
                            'Menu',

                        'quantity' =>
                            0,

                        'price' =>
                            (float) (
                                $item
                                    ->unit_price ??
                                $item->price ??
                                0
                            ),

                        'total' =>
                            0,
                    ];
                }

                $quantity =
                    (int)
                    $item->quantity;

                $unitPrice =
                    (float) (
                        $item->unit_price ??
                        $item->price ??
                        0
                    );

                $soldItemGroups[$key]['quantity'] +=
                    $quantity;

                $soldItemGroups[$key]['total'] +=
                    $unitPrice *
                    $quantity;
            }

            // =====================================================
            // BUNDLE TOTAL
            // =====================================================

            foreach (
                $soldItemGroups as $key => &$soldItem
            ) {

                if (
                    $soldItem['item_type'] ===
                    'bundle'
                ) {

                    $soldItem['total'] =
                        $soldItem['price'] *
                        $soldItem['quantity'];
                }
            }

            unset(
                $soldItem
            );

            // =====================================================
            // SORT SOLD ITEMS
            // =====================================================

            $soldItems =
                collect(
                    $soldItemGroups
                )
                ->sortByDesc(
                    'quantity'
                )
                ->values()
                ->take(10);

            // =====================================================
            // RECENT ORDERS
            // =====================================================

            $recentOrders =
                Order::with([
                    'table',
                    'items.menuItem',
                    'items.bundle',
                ])
                ->latest()
                ->limit(5)
                ->get()
                ->map(
                    function ($order) {

                        return [
                            'id' =>
                                $order->id,

                            'order_number' =>
                                $order->order_number,

                            'customer_name' =>
                                $order->customer_name,

                            'table' =>
                                $order->table,

                            'order_type' =>
                                $order->order_type,

                            'payment_method' =>
                                $order->payment_method,

                            'payment_status' =>
                                $order->payment_status,

                            'status' =>
                                $order->status,

                            'total_amount' =>
                                (float)
                                $order->total_amount,

                            'created_at' =>
                                $order->created_at,

                            'items' =>
                                $order->items
                                    ->map(
                                        function ($item) {

                                            return [
                                                'id' =>
                                                    $item->id,

                                                'menu_item' =>
                                                    $item->menuItem,

                                                'bundle' =>
                                                    $item->bundle,

                                                'bundle_id' =>
                                                    $item->bundle_id,

                                                'item_type' =>
                                                    $item->item_type,

                                                'quantity' =>
                                                    (int)
                                                    $item->quantity,

                                                'price' =>
                                                    (float) (
                                                        $item->unit_price ??
                                                        $item->price ??
                                                        0
                                                    ),
                                            ];
                                        }
                                    )
                                    ->values(),
                        ];
                    }
                )
                ->values();

            // =====================================================
            // RESPONSE
            // =====================================================

            $data = [

                'total_orders' =>
                    $totalOrders,

                'total_revenue' =>
                    (float)
                    $totalRevenue,

                'active_menu_count' =>
                    $activeProductCount,

                'active_menu_items' =>
                    $activeMenuCount,

                'active_bundles' =>
                    $activeBundleCount,

                'today_orders' =>
                    $totalOrders,

                'today_revenue' =>
                    (float)
                    $totalRevenue,

                'today_payment_methods' =>
                    $paymentBreakdown,

                'today_sold_items' =>
                    $soldItems,

                'recent_orders' =>
                    $recentOrders,

                'filter' => [
                    'period' =>
                        $period ??
                        null,

                    'start_date' =>
                        $startDate ??
                        null,

                    'end_date' =>
                        $endDate ??
                        null,

                    'timezone' =>
                        $timezone,
                ],
            ];

            return $this->successResponse(
                $data,
                'Berhasil mengambil data statistik dashboard admin'
            );

        } catch (\Exception $e) {

            return $this->errorResponse(
                'Terjadi kesalahan saat mengambil data dashboard: ' .
                    $e->getMessage(),
                null,
                500
            );
        }
    }
}
