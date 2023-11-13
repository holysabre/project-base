<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductionRequest;
use App\Jobs\MakeProductionXml;
use App\Models\Image;
use App\Models\Production;
use App\Models\ProductionHotspot;
use App\Models\ProductionMedia;
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
        $production->load(['production_media', 'production_media.media', 'production_media.media.thumb_image', 'xml_image']);

        $production->production_media->each(function ($production_media) {
            $production_media->media->thumb_image->path = env('QINIU_DOMAIN') . '/' . $production_media->media->thumb_image->path;
        });

        $production->xml_image->path = env('QINIU_DOMAIN') . '/' . $production->xml_image->path;

        // $slics_images = Image::query()->where('type', 'slice')
        //     ->where('rel_type', 'App\Models\Media')
        //     ->where('rel_id', $production->media_id)
        //     ->pluck('path');

        return json_response(200, '', ['detail' => $production]);
    }

    public function store(ProductionRequest $request)
    {
        $user_id = auth('api')->id();

        DB::beginTransaction();
        try {
            $production = new Production();
            $production->fill($request->all());
            $production->user_id = $user_id;
            $production->save();

            $inserting_media_list = [];
            for ($i = 0; $i < count($request->media_ids); $i++) {
                $media_id = $request->media_ids[$i];
                $inserting_media_list[] = new ProductionMedia([
                    'media_id' => $media_id,
                    'is_main' => $i == 0 ? 1 : 0,
                ]);
            }
            $production->production_media()->saveMany($inserting_media_list);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e->getFile(), $e->getLine()]);
            throw new CustomException('创建作品失败');
        }

        dispatch(new MakeProductionXml($production));

        return json_response(200);
    }

    public function update(ProductionRequest $request, Production $production)
    {
        $production->fill($request->all());
        $production->save();

        dispatch(new MakeProductionXml($production));

        return json_response();
    }

    public function destroy(Request $request, Production $production)
    {
        $production->delete();

        return json_response();
    }
}
