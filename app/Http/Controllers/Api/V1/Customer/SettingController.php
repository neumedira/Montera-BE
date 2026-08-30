<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\PaymentSetting;
use App\Traits\ApiResponse;

class SettingController extends Controller
{
    use ApiResponse;

    // ==========================================
    // CUSTOMER — SETTINGS
    // GET /api/v1/customer/settings
    // ==========================================

    public function index()
    {
        $data = [
            'business_profile' => BusinessProfile::first(),

            'payment_settings' => PaymentSetting::where(
                'is_active',
                true
            )->get(),
        ];

        return $this->successResponse(
            $data,
            'Customer settings retrieved.'
        );
    }
}

