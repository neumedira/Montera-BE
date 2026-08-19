<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Http\Requests\StoreBundleRequest;
use App\Http\Requests\UpdateBundleRequest;

class BundleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBundleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Bundle $bundle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBundleRequest $request, Bundle $bundle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bundle $bundle)
    {
        //
    }
}
