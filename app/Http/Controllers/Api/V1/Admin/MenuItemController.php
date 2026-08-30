<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Events\MenuUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\MenuItem;
use App\Traits\ApiResponse;

class MenuItemController extends Controller
{
    use ApiResponse;

    /**
     * Ambil semua menu.
     */
    public function index()
    {
        $menuItems = MenuItem::with([
            'category',
            'addons',
        ])->get();

        return $this->successResponse(
            $menuItems,
            'Menu items retrieved.'
        );
    }

    /**
     * Tambah menu baru.
     */
    public function store(StoreMenuItemRequest $request)
    {
        $data = $request->validated();

        // ================================================
        // UPLOAD FOTO
        // ================================================

        if ($request->hasFile('photo')) {
            $path = $request
                ->file('photo')
                ->store('menu-items', 'public');

            $data['photo_url'] = asset(
                'storage/' . $path
            );
        }

        // ================================================
        // CREATE MENU
        // ================================================

        $menuItem = MenuItem::create($data);

        // ================================================
        // SYNC ADD-ON
        // ================================================

        if (isset($data['addon_ids'])) {
            $menuItem->addons()->sync(
                $data['addon_ids']
            );
        }

        // ================================================
        // LOAD RELATION
        // ================================================

        $menuItem->load([
            'category',
            'addons',
        ]);

        // ================================================
        // BROADCAST KE CUSTOMER
        // ================================================

        event(
            new MenuUpdated($menuItem)
        );

        return $this->successResponse(
            $menuItem,
            'Menu item created.',
            201
        );
    }

    /**
     * Detail menu.
     */
    public function show(MenuItem $menuItem)
    {
        $menuItem->load([
            'category',
            'addons',
        ]);

        return $this->successResponse(
            $menuItem,
            'Menu item retrieved.'
        );
    }

    /**
     * Update menu.
     */
    public function update(
        UpdateMenuItemRequest $request,
        MenuItem $menuItem
    ) {
        $data = $request->validated();

        // ================================================
        // UPLOAD FOTO BARU
        // ================================================

        if ($request->hasFile('photo')) {
            $path = $request
                ->file('photo')
                ->store('menu-items', 'public');

            $data['photo_url'] = asset(
                'storage/' . $path
            );
        }

        // ================================================
        // UPDATE MENU
        // ================================================

        $menuItem->update($data);

        // ================================================
        // SYNC ADD-ON
        // ================================================

        if (isset($data['addon_ids'])) {
            $menuItem->addons()->sync(
                $data['addon_ids']
            );
        }

        // ================================================
        // LOAD DATA TERBARU
        // ================================================

        $menuItem->load([
            'category',
            'addons',
        ]);

        // ================================================
        // BROADCAST KE CUSTOMER
        // ================================================

        event(
            new MenuUpdated($menuItem)
        );

        return $this->successResponse(
            $menuItem,
            'Menu item updated.'
        );
    }

    /**
     * Hapus menu.
     */
    public function destroy(MenuItem $menuItem)
    {
        $deletedMenuId = $menuItem->id;

        // ================================================
        // DELETE
        // ================================================

        $menuItem->delete();

        // ================================================
        // BROADCAST DELETE KE CUSTOMER
        // ================================================

        event(
            new MenuUpdated([
                'id' => $deletedMenuId,
                'deleted' => true,
            ])
        );

        return $this->successResponse(
            null,
            'Menu item deleted.'
        );
    }
}