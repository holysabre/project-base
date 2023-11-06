<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductionRequest;
use App\Models\Production;
use App\Models\ProductionHotspot;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductionsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(array_keys(Production::$mapType))],
            'production_group_id' => 'sometimes',
            'keywords' => 'sometimes',
        ]);

        $user_id = auth('api')->id();

        $builder = Production::query()->withUserId($user_id)
            ->where('type', $request->type);

        if (!empty($request->production_group_id)) {
            $builder->where('production_group_id', $request->production_group_id);
        }

        if (!empty($request->keywords)) {
            $builder->where('title', 'like', '%' . $request->keywords . '%');
        }

        $list = $builder->paginate($request->input('per_page', 10));

        $items = $list->items();

        return json_response(200, '', ['list' => $items], $list->total());
    }

    public function show(Request $request, Production $production)
    {
        return json_response(200, '', ['detail' => $production]);
    }

    public function store(ProductionRequest $request)
    {
        $user_id = auth('api')->id();

        DB::beginTransaction();
        try {
            $media = new Production();
            $media->fill($request->all());
            $media->user_id = $user_id;
            $media->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e->getFile(), $e->getLine()]);
            throw new CustomException('创建作品失败');
        }

        return json_response(200);
    }

    public function update(ProductionRequest $request, Production $production)
    {
        $production->fill($request->all());
        $production->save();

        if (!empty($request->hotspots)) {
            $saving_hotspots = [];
            foreach ($request->hotspots as $hotspot_params) {
                $hotspot = new ProductionHotspot();
                $hotspot->fill(Arr::only($hotspot_params, ['name', 'ath', 'atv']));
                $saving_hotspots[] = $hotspot;
            }
            $production->production_hotspots()->saveMany($saving_hotspots);
        }

        return json_response();
    }

    public function destroy(Request $request, Production $production)
    {
        $production->delete();

        return json_response();
    }

    public function clearHotspots(Request $request, Production $production)
    {
        $production->production_hotspots()->delete();

        return json_response();
    }
}
