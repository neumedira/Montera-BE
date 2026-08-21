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
        $menuItem = MenuItem::create($request->validated());

        return $this->successResponse($menuItem, 'Menu item created.', 201);
    }


    public function show(MenuItem $menuItem)
    {
        $menuItem->load('category');

        return $this->successResponse($menuItem, 'Menu item retrieved.');
    }


    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem)
    {
        $menuItem->update($request->validated());

        return $this->successResponse($menuItem, 'Menu item updated.');
    }


    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return $this->successResponse(null, 'Menu item deleted.');
    }
}
