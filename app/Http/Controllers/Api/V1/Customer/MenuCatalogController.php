<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Bundle;
use App\Models\BusinessProfile;
use App\Models\PaymentSetting;
use App\Models\Table;
use App\Traits\ApiResponse;

class MenuCatalogController extends Controller
{
    use ApiResponse;

    // ==========================================
    // CUSTOMER — SETTINGS
    // GET /api/v1/customer/settings
    // ==========================================

    public function settings()
    {
        $data = [
            'business_profile' => BusinessProfile::first(),
            'payment_settings' => PaymentSetting::all(),
        ];

        return $this->successResponse(
            $data,
            'Customer settings retrieved.'
        );
    }

    // ==========================================
    // CUSTOMER — SCAN QR TABLE
    // GET /api/v1/customer/scan/{token}
    // ==========================================

    public function scanTable($token)
    {
        $table = Table::where(
            'qr_token',
            $token
        )
            ->where(
                'is_active',
                true
            )
            ->first();

        if (!$table) {
            return $this->errorResponse(
                'QR meja tidak valid atau meja tidak tersedia.',
                404
            );
        }

        return $this->successResponse(
            [
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'qr_token' => $table->qr_token,
            ],
            'Meja berhasil ditemukan.'
        );
    }

    // ==========================================
    // CUSTOMER — MENU CATALOG
    // GET /api/v1/customer/menus
    // ==========================================

    public function index()
    {
        $menus = MenuItem::with([
            'category',
            'addons'
        ])
            ->where(
                'is_active',
                true
            )
            ->get();

        return $this->successResponse(
            $menus,
            'Customer menus retrieved.'
        );
    }

    // ==========================================
    // CUSTOMER — BUNDLE CATALOG
    // GET /api/v1/customer/bundles
    // ==========================================

    public function bundles()
    {
        $bundles = Bundle::with([
            'items.menuItem.category',
            'items.menuItem.addons'
        ])
            ->where(
                'is_active',
                true
            )
            ->get();

        return $this->successResponse(
            $bundles,
            'Customer bundles retrieved.'
        );
    }

    // ==========================================
    // CUSTOMER — BUNDLE DETAIL
    // GET /api/v1/customer/bundles/{bundle}
    // ==========================================

    public function bundleDetail(
        Bundle $bundle
    ) {
        // Jangan tampilkan bundle yang sudah nonaktif
        if (!$bundle->is_active) {
            return $this->errorResponse(
                'Bundle tidak tersedia.',
                404
            );
        }

        $bundle->load([
            'items.menuItem.category',
            'items.menuItem.addons'
        ]);

        return $this->successResponse(
            $bundle,
            'Customer bundle detail retrieved.'
        );
    }
}

