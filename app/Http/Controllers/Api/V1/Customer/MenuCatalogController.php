<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Traits\ApiResponse;

class MenuCatalogController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $menus = MenuItem::with([
            'category',
            'addons'
        ])->get();

        return $this->successResponse($menus);
    }
}
