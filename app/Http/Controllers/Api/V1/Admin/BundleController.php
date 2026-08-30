<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Http\Requests\StoreBundleRequest;
use App\Http\Requests\UpdateBundleRequest;
use App\Traits\ApiResponse;

class BundleController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $bundles = Bundle::with('items')->get();

        return $this->successResponse($bundles, 'Bundles retrieved.');
    }


    public function store(StoreBundleRequest $request)
    {
        $data = $request->validated();

    if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('bundles', 'public');
        $data['photo'] = asset('storage/' . $path);
    }

    $bundle = Bundle::create($data);


    if (isset($data['items'])) {
        foreach ($data['items'] as $item) {
            $bundle->items()->create($item);
        }
    }

        return $this->successResponse($bundle, 'Bundle created.', 201);
    }


    public function show(Bundle $bundle)
    {
        $bundle->load('items');

        return $this->successResponse($bundle, 'Bundle retrieved.');
    }


    public function update(UpdateBundleRequest $request, Bundle $bundle)
    {
        $data = $request->validated();

    if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('bundles', 'public');
        $data['photo'] = asset('storage/' . $path);
    }

    $bundle->update($data);


    if (isset($data['items'])) {
        $bundle->items()->delete();
        foreach ($data['items'] as $item) {
            $bundle->items()->create($item);
        }
    }

        return $this->successResponse($bundle, 'Bundle updated.');
    }


    public function destroy(Bundle $bundle)
    {
        $bundle->delete();

        return $this->successResponse(null, 'Bundle deleted.');
    }
}
