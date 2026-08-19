<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessProfile;
use App\Models\TaxSetting;
use App\Models\PaymentSetting;
use App\Models\ReceiptSetting;
use App\Traits\ApiResponse;

class SettingController extends Controller
{
    use ApiResponse;

    // Menampilkan seluruh data pengaturan sekaligus
    public function index()
    {
        $data = [
            'business_profile' => BusinessProfile::first(),
            'tax_setting' => TaxSetting::first(),
            'payment_settings' => PaymentSetting::all(), // Menampilkan Cash & QRIS
            'receipt_setting' => ReceiptSetting::first(),
        ];

        return $this->successResponse($data, 'Data pengaturan berhasil ditarik');
    }

    // Memperbarui data pengaturan (Bisa salah satu, bisa semuanya)
    public function update(Request $request)
    {
        // 1. Update Profil Usaha
        if ($request->has('business_profile')) {
            $profile = BusinessProfile::first() ?? new BusinessProfile();
            $profile->cafe_name = $request->input('business_profile.cafe_name', $profile->cafe_name);
            $profile->address = $request->input('business_profile.address', $profile->address);
            $profile->whatsapp_number = $request->input('business_profile.whatsapp_number', $profile->whatsapp_number);
            $profile->save();
        }

        // 2. Update Pengaturan Pajak
        if ($request->has('tax_setting')) {
            $tax = TaxSetting::first() ?? new TaxSetting();
            $tax->tax_percentage = $request->input('tax_setting.tax_percentage', $tax->tax_percentage);
            $tax->service_charge_percentage = $request->input('tax_setting.service_charge_percentage', $tax->service_charge_percentage);
            $tax->save();
        }

        // 3. Update Pengaturan Struk
        if ($request->has('receipt_setting')) {
            $receipt = ReceiptSetting::first() ?? new ReceiptSetting();
            $receipt->show_logo = $request->input('receipt_setting.show_logo', $receipt->show_logo);
            $receipt->header_text = $request->input('receipt_setting.header_text', $receipt->header_text);
            $receipt->footer_text = $request->input('receipt_setting.footer_text', $receipt->footer_text);
            $receipt->save();
        }

        // 4. Update Metode Pembayaran (Cash/QRIS Aktif atau Tidak)
        if ($request->has('payment_settings')) {
            foreach ($request->input('payment_settings') as $payment) {
                PaymentSetting::updateOrCreate(
                    ['method' => $payment['method']],
                    [
                        'is_active' => $payment['is_active'],
                        'provider_note' => $payment['provider_note'] ?? null
                    ]
                );
            }
        }

        return $this->successResponse(null, 'Pengaturan berhasil diperbarui');
    }
}
