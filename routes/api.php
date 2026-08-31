<?php

use Illuminate\Support\Facades\Route;

// =========================================================
// ADMIN CONTROLLERS
// =========================================================

use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\SettingController;
use App\Http\Controllers\Api\V1\Admin\TableController;
use App\Http\Controllers\Api\V1\Admin\MenuCategoryController;
use App\Http\Controllers\Api\V1\Admin\MenuItemController;
use App\Http\Controllers\Api\V1\Admin\AddonController;
use App\Http\Controllers\Api\V1\Admin\BundleController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\OrderAdminController;
use App\Http\Controllers\Api\V1\Admin\NotificationController;

// =========================================================
// CUSTOMER CONTROLLERS
// =========================================================

use App\Http\Controllers\Api\V1\Customer\MenuCatalogController;
use App\Http\Controllers\Api\V1\Customer\SettingController as CustomerSettingController;
use App\Http\Controllers\Api\V1\Customer\OrderController;


// =========================================================
// API ADMIN
// PREFIX: /api/v1/admin
// =========================================================

Route::prefix('v1/admin')->group(function () {

    // =====================================================
    // AUTH
    // =====================================================

    Route::post(
        'login',
        [AuthController::class, 'login']
    );

    // =====================================================
    // PUBLIC SETTINGS
    // =====================================================

    Route::get(
        'settings',
        [SettingController::class, 'index']
    );

    // =====================================================
    // PROTECTED ADMIN ROUTES
    // =====================================================

    Route::middleware('auth:sanctum')->group(function () {

        // =================================================
        // AUTH
        // =================================================

        Route::post(
            'logout',
            [AuthController::class, 'logout']
        );

        // =================================================
        // DASHBOARD
        // =================================================

        Route::get(
            'dashboard',
            [DashboardController::class, 'index']
        );

        // =================================================
        // SETTINGS
        // =================================================

        Route::post(
            'settings',
            [SettingController::class, 'update']
        );

        Route::delete(
            'settings/payment-methods/{id}',
            [SettingController::class, 'destroyPaymentMethod']
        );

        // =================================================
        // NOTIFICATIONS
        // =================================================

        // Ambil semua notification yang belum dibaca
        Route::get(
            'notifications',
            [NotificationController::class, 'index']
        );

        // Tandai satu notification sebagai sudah dibaca
        Route::patch(
            'notifications/{id}/read',
            [NotificationController::class, 'markAsRead']
        );

        // =================================================
        // MASTER DATA
        // =================================================

        Route::get('tables/{table}/print', [TableController::class, 'printQr']);

        Route::apiResource(
            'tables',
            TableController::class
        );

        Route::apiResource(
            'menu-categories',
            MenuCategoryController::class
        );

        Route::apiResource(
            'menu-items',
            MenuItemController::class
        );

        Route::apiResource(
            'addons',
            AddonController::class
        );

        Route::apiResource(
            'bundles',
            BundleController::class
        );

        // =================================================
        // ORDER MONITORING
        // =================================================

        // Update status order
        Route::patch(
            'orders/{id}/status',
            [OrderAdminController::class, 'updateStatus']
        );

        // Order list + detail
        Route::apiResource(
            'orders',
            OrderAdminController::class
        )->except([
                    'store',
                    'destroy',
                ]);

        // =================================================
        // VERIFY QRIS PAYMENT
        // =================================================

        Route::patch(
            'orders/{order}/verify-payment',
            [OrderAdminController::class, 'verifyPayment']
        );
    });
});


// =========================================================
// API CUSTOMER
// PREFIX: /api/v1/customer
// =========================================================

Route::prefix('v1/customer')->group(function () {

    // =====================================================
    // SETTINGS
    // =====================================================

    Route::get(
        'settings',
        [CustomerSettingController::class, 'index']
    );

    // =====================================================
    // SCAN TABLE QR
    // GET /api/v1/customer/scan/{token}
    // =====================================================

    Route::get(
        'scan/{token}',
        [MenuCatalogController::class, 'scanTable']
    );

    // =====================================================
    // MENU
    // =====================================================

    Route::get(
        'menus',
        [MenuCatalogController::class, 'index']
    );

    // =====================================================
    // BUNDLE LIST
    // =====================================================

    Route::get(
        'bundles',
        [MenuCatalogController::class, 'bundles']
    );

    // =====================================================
    // BUNDLE DETAIL
    // =====================================================

    Route::get(
        'bundles/{bundle}',
        [MenuCatalogController::class, 'bundleDetail']
    );

    // =====================================================
    // CUSTOMER ORDER
    // =====================================================

    Route::post(
        'orders',
        [OrderController::class, 'store']
    );
});


// =========================================================
// WEBHOOK PAYMENT
// =========================================================
//
// Route::post(
//     'v1/webhook/qris',
//     [WebhookController::class, 'handleQrisWebhook']
// );