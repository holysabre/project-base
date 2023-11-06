<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductionGroupRequest;
use App\Models\Production;
use App\Models\ProductionGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductionGroupsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(array_keys(Production::$mapType))]
        ]);

        $user_id = auth('api')->id();

        $builder = ProductionGroup::query()->withUserId($user_id)->where('type', $request->type)->orderByDesc('sort');

        $list = $builder->paginate($request->input('per_page', 10));

        return json_response(200, '', ['list' => $list->items()], $list->total());
    }

    public function store(ProductionGroupRequest $request, ProductionGroup $production_group)
    {
        $user_id = auth('api')->id();

        $production_group->fill($request->all());
        $production_group->user_id = $user_id;
        $production_group->save();

        return json_response();
    }

    public function update(ProductionGroupRequest $request, ProductionGroup $production_group)
    {
        $production_group->fill($request->all());
        $production_group->save();

        return json_response();
    }

    public function destroy(Request $request, ProductionGroup $production_group)
    {
        $production_group->delete();

        return json_response();
    }
}
