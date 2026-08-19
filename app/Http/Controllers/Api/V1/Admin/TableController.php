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
        // Validasi sudah otomatis ditangani oleh StoreTableRequest

        $table = Table::create([
            'table_number' => $request->table_number,
            'qr_token' => Str::random(64), // Generate 64 karakter acak
            'qr_code_url' => $request->qr_code_url, // Opsional jika admin mau custom link
            'is_active' => true // Default selalu aktif saat baru dibuat
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
            'qr_code_url' => $request->qr_code_url ?? $table->qr_code_url,
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
