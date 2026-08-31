<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Addon;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\TaxSetting;
use App\Events\NewNotificationEvent;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use ApiResponse;

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated) {

            $subtotal = 0;

            // =====================================================
            // CREATE ORDER
            // =====================================================

            $order = Order::create([
                'order_number' =>
                    $this->generateOrderNumber(),

                'table_id' =>
                    $validated['table_id'] ?? null,

                'order_type' =>
                    $validated['order_type'],

                'customer_name' =>
                    $validated['customer_name'],

                // =================================================
                // GLOBAL ORDER NOTES
                // =================================================

                'notes' =>
                    $validated['notes'] ?? null,

                'payment_method' =>
                    $validated['payment_method'],

                'payment_status' =>
                    'unpaid',

                'subtotal' =>
                    0,

                'tax_amount' =>
                    0,

                'service_charge_amount' =>
                    0,

                'total_amount' =>
                    0,
            ]);

            // =====================================================
            // ORDER ITEMS
            // =====================================================

            foreach ($validated['items'] as $item) {

                // =================================================
                // FIND MENU
                // =================================================

                $menuItem =
                    MenuItem::where(
                        'id',
                        $item['menu_item_id']
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->first();

                if (!$menuItem) {
                    abort(
                        422,
                        'Menu item is not available.'
                    );
                }

                // =================================================
                // PRICE
                // =================================================

                $unitPrice =
                    $menuItem->price;

                $itemSubtotal =
                    $unitPrice *
                    $item['quantity'];

                // =================================================
                // BUNDLE ID
                // =================================================

                $bundleId =
                    $item['bundle_id'] ?? null;

                // =================================================
                // CREATE ORDER ITEM
                // =================================================

                $orderItem =
                    OrderItem::create([
                        'order_id' =>
                            $order->id,

                        'item_type' =>
                            $bundleId
                                ? 'bundle'
                                : 'menu',

                        'menu_item_id' =>
                            $menuItem->id,

                        'bundle_id' =>
                            $bundleId,

                        'item_name' =>
                            $menuItem->name,

                        'unit_price' =>
                            $unitPrice,

                        'quantity' =>
                            $item['quantity'],

                        'subtotal' =>
                            $itemSubtotal,

                        // Catatan per item/menu
                        'notes' =>
                            $item['notes'] ?? null,
                    ]);

                // =================================================
                // ADDONS
                // =================================================

                $addonIds =
                    $item['addon_ids'] ?? [];

                if (!empty($addonIds)) {

                    $addons =
                        Addon::whereIn(
                            'id',
                            $addonIds
                        )
                        ->where(
                            'is_active',
                            true
                        )
                        ->get();

                    if (
                        $addons->count() !==
                        count(
                            array_unique(
                                $addonIds
                            )
                        )
                    ) {
                        abort(
                            422,
                            'One or more addons are not available.'
                        );
                    }

                    $allowedAddonIds =
                        $menuItem
                            ->addons()
                            ->where(
                                'addons.is_active',
                                true
                            )
                            ->pluck(
                                'addons.id'
                            );

                    foreach (
                        $addons as $addon
                    ) {

                        if (
                            !$allowedAddonIds
                                ->contains(
                                    $addon->id
                                )
                        ) {
                            abort(
                                422,
                                "Addon {$addon->name} is not available for {$menuItem->name}."
                            );
                        }

                        OrderItemAddon::create([
                            'order_item_id' =>
                                $orderItem->id,

                            'addon_id' =>
                                $addon->id,

                            'addon_name' =>
                                $addon->name,

                            'addon_price' =>
                                $addon->price,
                        ]);

                        $itemSubtotal +=
                            $addon->price *
                            $item['quantity'];
                    }
                }

                // =================================================
                // UPDATE ITEM SUBTOTAL
                // =================================================

                $orderItem->update([
                    'subtotal' =>
                        $itemSubtotal,
                ]);

                // =================================================
                // ADD TO ORDER SUBTOTAL
                // =================================================

                $subtotal +=
                    $itemSubtotal;
            }

            // =====================================================
            // TAX / SERVICE CHARGE
            // =====================================================

            $taxSetting =
                TaxSetting::latest()->first();

            $taxPercentage =
                $taxSetting?->tax_percentage ?? 0;

            $serviceChargePercentage =
                $taxSetting?->service_charge_percentage ?? 0;

            $taxAmount =
                $subtotal *
                ($taxPercentage / 100);

            $serviceChargeAmount =
                $subtotal *
                ($serviceChargePercentage / 100);

            $totalAmount =
                $subtotal +
                $taxAmount +
                $serviceChargeAmount;

            // =====================================================
            // UPDATE ORDER TOTAL
            // =====================================================

            $order->update([
                'subtotal' =>
                    $subtotal,

                'tax_amount' =>
                    $taxAmount,

                'service_charge_amount' =>
                    $serviceChargeAmount,

                'total_amount' =>
                    $totalAmount,
            ]);

            return $order;
        });

        // =========================================================
        // LOAD RELATIONS
        // =========================================================

        $order->load([
            'table',
            'items.menuItem',
            'items.addons.addon',
        ]);

        // =========================================================
        // CREATE NOTIFICATION
        // =========================================================

        $notificationId =
            DB::table('notifications')
                ->insertGetId([
                    'order_id' =>
                        $order->id,

                    'message' =>
                        "Pesanan #{$order->order_number} dari {$order->customer_name} telah masuk.",

                    'is_read' =>
                        false,

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

        // =========================================================
        // GET NOTIFICATION
        // =========================================================

        $notification =
            DB::table('notifications')
                ->where(
                    'id',
                    $notificationId
                )
                ->first();

        // =========================================================
        // REALTIME PAYLOAD
        // =========================================================

        $notification->title =
            'Pesanan Baru';

        $notification->order_number =
            $order->order_number;

        $notification->message =
            'Ada pesanan baru masuk';

        // =========================================================
        // BROADCAST REALTIME
        // =========================================================

        event(
            new NewNotificationEvent(
                $notification
            )
        );

        // =========================================================
        // RESPONSE
        // =========================================================

        return $this->successResponse(
            $order,
            'Order created successfully.',
            201
        );
    }

    // =========================================================
    // ORDER NUMBER
    // =========================================================

    private function generateOrderNumber(): string
    {
        do {

            $orderNumber =
                'ORD-' .
                strtoupper(
                    Str::random(12)
                );

        } while (
            Order::where(
                'order_number',
                $orderNumber
            )->exists()
        );

        return $orderNumber;
    }
}

