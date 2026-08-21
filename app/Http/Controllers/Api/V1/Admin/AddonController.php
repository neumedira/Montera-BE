<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Http\Requests\StoreAddonRequest;
use App\Http\Requests\UpdateAddonRequest;
use App\Traits\ApiResponse;

class AddonController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $addons = Addon::all();

        return $this->successResponse($addons, 'Addons retrieved.');

    }


    public function store(StoreAddonRequest $request)
    {
        $addon = Addon::create($request->validated());

        return $this->successResponse($addon, 'Addon created.', 201);
    }


    public function show(Addon $addon)
    {
        return $this->successResponse($addon, 'Addon retrieved.');
    }


    public function update(UpdateAddonRequest $request, Addon $addon)
    {
        $addon->update($request->validated());

        return $this->successResponse($addon, 'Addon updated.');
    }


    public function destroy(Addon $addon)
    {
        $addon->delete();

        return $this->successResponse(null, 'Addon deleted.');
    }
}
