<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Traits\ApiResponse;

class PaymentMethodController extends Controller
{
    use ApiResponse;

    /**
     * Get active payment methods for customer.
     */
    public function index()
    {
        $paymentMethods = PaymentSetting::where(
            'is_active',
            true
        )
            ->orderBy('id')
            ->get([
                'id',
                'method',
                'is_active',
                'provider_note',
                'qr_image_url',
            ]);

        return $this->successResponse(
            $paymentMethods,
            'Metode pembayaran berhasil ditarik'
        );
    }
}