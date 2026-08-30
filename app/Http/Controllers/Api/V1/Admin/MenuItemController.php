<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Traits\ApiResponse;

class MenuItemController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $menuItems = MenuItem::with('category')->get();

        return $this->successResponse($menuItems, 'Menu items retrieved.');
    }


    public function store(StoreMenuItemRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('menu-items', 'public');
            $data['photo'] = asset('storage/' . $path);
        }

        $menuItem = MenuItem::create($data);

        return $this->successResponse($menuItem, 'Menu item created.', 201);
    }


    public function show(MenuItem $menuItem)
    {
        $menuItem->load('category');

        return $this->successResponse($menuItem, 'Menu item retrieved.');
    }


    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem)
    {
        $data = $request->validated();

        // Handle photo upload if a file is provided
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('menu-items', 'public');
            $data['photo'] = asset('storage/' . $path);
        }

        $menuItem->update($data);

        return $this->successResponse($menuItem, 'Menu item updated.');
    }


    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return $this->successResponse(null, 'Menu item deleted.');
    }
}
