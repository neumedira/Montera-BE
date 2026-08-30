<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\BundleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Http\Requests\StoreBundleRequest;
use App\Http\Requests\UpdateBundleRequest;
use App\Traits\ApiResponse;

class BundleController extends Controller
{
    use ApiResponse;

    /**
     * GET /admin/bundles
     */
    public function index()
    {
        $bundles = Bundle::with([
            'items.menuItem.addons'
        ])->get();

        return $this->successResponse(
            $bundles,
            'Bundles retrieved.'
        );
    }

    /**
     * POST /admin/bundles
     */
    public function store(StoreBundleRequest $request)
    {
        $data = $request->validated();

        // =====================================================
        // FOTO
        // =====================================================

        if ($request->hasFile('photo')) {
            $path = $request
                ->file('photo')
                ->store('bundles', 'public');

            $data['photo_url'] = asset(
                'storage/' . $path
            );
        }

        // =====================================================
        // CREATE BUNDLE
        // =====================================================

        $bundle = Bundle::create($data);

        // =====================================================
        // CREATE ITEM BUNDLE
        // =====================================================

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $bundle->items()->create($item);
            }
        }

        // =====================================================
        // LOAD RELATION
        // =====================================================

        $bundle->load([
            'items.menuItem.addons'
        ]);

        // =====================================================
        // REALTIME
        // =====================================================

        event(new BundleUpdated($bundle));

        return $this->successResponse(
            $bundle,
            'Bundle created.',
            201
        );
    }

    /**
     * GET /admin/bundles/{bundle}
     */
    public function show(Bundle $bundle)
    {
        $bundle->load([
            'items.menuItem.addons'
        ]);

        return $this->successResponse(
            $bundle,
            'Bundle retrieved.'
        );
    }

    /**
     * PUT /admin/bundles/{bundle}
     */
    public function update(
        UpdateBundleRequest $request,
        Bundle $bundle
    ) {
        $data = $request->validated();

        // =====================================================
        // FOTO BARU
        // =====================================================

        if ($request->hasFile('photo')) {
            $path = $request
                ->file('photo')
                ->store('bundles', 'public');

            $data['photo_url'] = asset(
                'storage/' . $path
            );
        }

        // =====================================================
        // UPDATE BUNDLE
        // =====================================================

        $bundle->update($data);

        // =====================================================
        // UPDATE ITEM BUNDLE
        // =====================================================

        if (isset($data['items'])) {
            $bundle->items()->delete();

            foreach ($data['items'] as $item) {
                $bundle->items()->create($item);
            }
        }

        // =====================================================
        // LOAD RELATION
        // =====================================================

        $bundle->load([
            'items.menuItem.addons'
        ]);

        // =====================================================
        // REALTIME
        // =====================================================

        event(new BundleUpdated($bundle));

        return $this->successResponse(
            $bundle,
            'Bundle updated.'
        );
    }

    /**
     * DELETE /admin/bundles/{bundle}
     */
    public function destroy(Bundle $bundle)
    {
        $deletedBundleId = $bundle->id;

        $bundle->delete();

        // =====================================================
        // REALTIME DELETE
        // =====================================================

        event(new BundleUpdated([
            'id' => $deletedBundleId,
            'deleted' => true,
        ]));

        return $this->successResponse(
            null,
            'Bundle deleted.'
        );
    }
}

