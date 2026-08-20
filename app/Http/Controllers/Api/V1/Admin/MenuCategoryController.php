<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Http\Requests\StoreMenuCategoryRequest;
use App\Http\Requests\UpdateMenuCategoryRequest;

class MenuCategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        //
    }


    public function store(StoreMenuCategoryRequest $request)
    {
        //
    }


    public function show(MenuCategory $menuCategory)
    {
        //
    }


    public function update(UpdateMenuCategoryRequest $request, MenuCategory $menuCategory)
    {
        //
    }

    
    public function destroy(MenuCategory $menuCategory)
    {
        //
    }
}
