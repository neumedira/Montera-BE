<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;
use Illuminate\Support\Str;
use App\Traits\ApiResponse;

class TableController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua data meja, urutkan dari yang terbaru
        $tables = Table::orderBy('created_at', 'desc')->get();

        return $this->successResponse($tables, 'Data meja berhasil ditarik');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTableRequest $request)
    {
        // 1. Generate token unik
        $token = Str::random(64);

        // 2. Ambil domain dari .env (Otomatis & Dinamis)
        $domainDepan = env('FRONTEND_URL', 'https://montera.cafe');
        $linkScan = $domainDepan . '/scan/' . $token;

        // 3. Simpan ke database
        $table = Table::create([
            'table_number' => $request->table_number,
            'qr_token' => $token,
            'qr_code_url' => $linkScan, // Otomatis terisi URL lengkap
            'is_active' => true
        ]);

        return $this->successResponse($table, 'Meja berhasil ditambahkan', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Table $table)
    {
        // Parameter (Table $table) otomatis mencari ID berkat Route Model Binding Laravel
        return $this->successResponse($table, 'Detail meja berhasil ditarik');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTableRequest $request, Table $table)
    {
        // Validasi sudah otomatis ditangani oleh UpdateTableRequest

        $table->update([
            'table_number' => $request->table_number ?? $table->table_number,
            'is_active' => $request->has('is_active') ? $request->is_active : $table->is_active,
        ]);

        return $this->successResponse($table, 'Data meja berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Table $table)
    {
        $table->delete();

        return $this->successResponse(null, 'Meja berhasil dihapus');
    }
}
