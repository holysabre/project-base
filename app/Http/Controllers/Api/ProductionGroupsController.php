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

    public function store(ProductionGroupRequest $request, ProductionGroup $media_group)
    {
        $user_id = auth('api')->id();

        $media_group->fill($request->all());
        $media_group->user_id = $user_id;
        $media_group->save();

        return json_response();
    }

    public function update(ProductionGroupRequest $request, ProductionGroup $media_group)
    {
        $media_group->fill($request->all());
        $media_group->save();

        return json_response();
    }

    public function destroy(Request $request, ProductionGroup $media_group)
    {
        $media_group->delete();

        return json_response();
    }
}
