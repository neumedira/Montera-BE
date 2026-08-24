<?php

use Illuminate\Support\Facades\Route;

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
use App\Http\Controllers\Api\WebhookController;

use App\Http\Controllers\Api\V1\Customer\MenuCatalogController;
use App\Http\Controllers\Api\V1\Customer\OrderController;

// ==========================================
// API ADMIN (PREFIX: /api/v1/admin)
// ==========================================
Route::prefix('v1/admin')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);

        // Dashboard & Settings
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('settings', [SettingController::class, 'index']);
        Route::post('settings', [SettingController::class, 'update']);

        // Notifications (Fitur Stefan - Dev 5)
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/mark-read', [NotificationController::class, 'markAsRead']);
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);

        // Master Data CRUD
        Route::apiResource('tables', TableController::class);
        Route::apiResource('menu-categories', MenuCategoryController::class);
        Route::apiResource('menu-items', MenuItemController::class);
        Route::apiResource('addons', AddonController::class);
        Route::apiResource('bundles', BundleController::class);

        // Order Monitoring & Update Status (Fitur Rafi - Dev 4)
        Route::patch('orders/{id}/status', [OrderAdminController::class, 'updateStatus']);
        Route::apiResource('orders', OrderAdminController::class)->except(['store', 'destroy']);
    });
});

// ==========================================
// API CUSTOMER (PREFIX: /api/v1/customer)
// ==========================================
Route::prefix('v1/customer')->group(function () {
    Route::get('menus', [MenuCatalogController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
});

// ==========================================
// WEBHOOK PAYMENT (FITUR STEFAN - DEV 5)
// ==========================================
// Tanpa auth & tanpa prefix customer agar URL bersih: /api/v1/webhook/qris
Route::post('v1/webhook/qris', [WebhookController::class, 'handleQrisWebhook']);
