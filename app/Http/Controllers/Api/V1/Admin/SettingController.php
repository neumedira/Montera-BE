<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessProfile;
use App\Models\TaxSetting;
use App\Models\PaymentSetting;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $data = [
            'business_profile' => BusinessProfile::first(),
            'tax_setting' => TaxSetting::first(),
            'payment_settings' => PaymentSetting::all(),
        ];

        // Fitur struk resmi dihapus dari respon

        return $this->successResponse($data, 'Data pengaturan berhasil ditarik');
    }

    public function update(Request $request)
    {
        // 1. Update Profil Usaha (Termasuk Sosmed)
        if ($request->has('business_profile')) {
            $profile = BusinessProfile::first() ?? new BusinessProfile();
            $profile->cafe_name = $request->input('business_profile.cafe_name', $profile->cafe_name);
            $profile->address = $request->input('business_profile.address', $profile->address);
            $profile->whatsapp_number = $request->input('business_profile.whatsapp_number', $profile->whatsapp_number);
            $profile->instagram = $request->input('business_profile.instagram', $profile->instagram);
            $profile->tiktok = $request->input('business_profile.tiktok', $profile->tiktok);
            $profile->save();
        }

        // 2. Update Pengaturan Pajak
        if ($request->has('tax_setting')) {
            $tax = TaxSetting::first() ?? new TaxSetting();
            $tax->tax_percentage = $request->input('tax_setting.tax_percentage', $tax->tax_percentage);
            $tax->service_charge_percentage = $request->input('tax_setting.service_charge_percentage', $tax->service_charge_percentage);
            $tax->save();
        }

        // 3. Update Metode Pembayaran & Upload Gambar QR
        if ($request->has('payment_settings')) {
            foreach ($request->input('payment_settings') as $index => $paymentData) {

                // Cari atau buat metode pembayaran baru (mendukung penambahan selain cash/qris)
                $payment = PaymentSetting::updateOrCreate(
                    ['method' => $paymentData['method']],
                    [
                        // filter_var berguna untuk memastikan input 'true'/'false' dari form-data terbaca boolean
                        'is_active' => filter_var($paymentData['is_active'], FILTER_VALIDATE_BOOLEAN),
                        'provider_note' => $paymentData['provider_note'] ?? null
                    ]
                );

                // Cek apakah Admin meng-upload gambar QR baru
                // Format request dari frontend: payment_settings[1][qr_image]
                $fileKey = "payment_settings.{$index}.qr_image";

                if ($request->hasFile($fileKey)) {
                    // Jika ada gambar QR lama, hapus dari folder storage agar tidak menumpuk
                    if ($payment->qr_image_url) {
                        Storage::disk('public')->delete($payment->qr_image_url);
                    }

                    // Simpan gambar QR baru ke folder storage/app/public/qris
                    $path = $request->file($fileKey)->store('qris', 'public');
                    $payment->update(['qr_image_url' => $path]);
                }
            }
        }

        return $this->successResponse(null, 'Pengaturan berhasil diperbarui');
    }

    public function destroyPaymentMethod($id)
    {
        $payment = PaymentSetting::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran tidak ditemukan'
            ], 404);
        }

        // Kalau ada gambar QR-nya, hapus juga file fisiknya biar storage gak penuh
        if ($payment->qr_image_url) {
            Storage::disk('public')->delete($payment->qr_image_url);
        }

        $payment->delete();

        return $this->successResponse(null, 'Metode pembayaran berhasil dihapus');
    }
}