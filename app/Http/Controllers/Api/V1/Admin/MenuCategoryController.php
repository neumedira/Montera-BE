<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Http\Requests\StoreMenuCategoryRequest;
use App\Http\Requests\UpdateMenuCategoryRequest;
use App\Traits\ApiResponse;

class MenuCategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $categories = MenuCategory::all();
        return $this->successResponse($categories, 'Menu categories retrieved.');
    }


    public function store(StoreMenuCategoryRequest $request)
    {
        $category = MenuCategory::create($request->validated());
        return $this->successResponse($category, 'Menu category created.', 201);
    }


    public function show(MenuCategory $menuCategory)
    {
        return $this->successResponse($menuCategory, 'Menu category retrieved.');
    }


    public function update(UpdateMenuCategoryRequest $request, MenuCategory $menuCategory)
    {
        $menuCategory->update($request->validated());
        return $this->successResponse($menuCategory, 'Menu category updated.');
    }


    public function destroy(MenuCategory $menuCategory)
    {
        $menuCategory->delete();
        return $this->successResponse(null, 'Menu category deleted.');
    }
}
