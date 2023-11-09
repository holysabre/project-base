<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Production;
use App\Models\ProductionMedia;
use App\Models\ProductionMediaHotspot;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * 场景
 */
class ProductionMediaController extends Controller
{
    public function index(Request $request, Production $production)
    {
        $production->load(['production_media', 'production_media.media', 'production_media.media.thumb_image']);

        $production->production_media->each(function ($production_media) {
            $production_media->media->thumb_image->path = env('QINIU_DOMAIN') . '/' . $production_media->media->thumb_image->path;
            $production_media->media->xml_image->path = env('QINIU_DOMAIN') . '/' . $production_media->media->xml_image->path;
        });

        $list = $production->production_media;

        return json_response(200, '', ['list' => $list], $list->count());
    }

    public function show(Request $request, Production $production, ProductionMedia $production_media)
    {
        $production_media->load(['production_media_hotspots']);

        $production_media->media->thumb_image->path = env('QINIU_DOMAIN') . '/' . $production_media->media->thumb_image->path;
        $production_media->media->xml_image->path = env('QINIU_DOMAIN') . '/' . $production_media->media->xml_image->path;

        return json_response(200, '', ['detail' => $production_media]);
    }

    public function store(Request $request, Production $production)
    {
        $request->validate([
            'media_ids' => 'required|array',
        ]);

        $exists_media_ids = $production->production_media->pluck('media_id');

        $media_ids = Media::query()->whereIn('id', $request->media_ids)->whereNotIn('id', $exists_media_ids)->pluck('id');
        $data = [];
        foreach ($media_ids as $media_id) {
            $data[] = new ProductionMedia(['media_id' => $media_id]);
        }
        $production->production_media()->saveMany($data);

        return json_response();
    }

    public function destroy(Request $request, Production $production, ProductionMedia $production_media)
    {
        $production_media->delete();

        return json_response();
    }

    public function saveHotspots(Request $request, Production $production, ProductionMedia $production_media)
    {
        $request->validate([
            'hotspots' => 'sometimes|array',
            'hotspots.*.name' => 'required',
            'hotspots.*.ath' => 'required',
            'hotspots.*.atv' => 'required',
            'hotspots.*.linkedsence' => 'sometimes',
        ]);

        DB::beginTransaction();
        try {
            $production_media->production_media_hotspots()->delete();

            $saving_hotspots = [];
            foreach ($request->hotspots as $hotspot_params) {
                $hotspot = new ProductionMediaHotspot();
                $hotspot->fill(Arr::only($hotspot_params, ['name', 'ath', 'atv']));
                if (!empty($hotspot_params['linkedscene'])) {
                    $hotspot->linkedscene = $hotspot_params['linkedscene'];
                }
                $saving_hotspots[] = $hotspot;
            }
            $production_media->production_media_hotspots()->saveMany($saving_hotspots);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new CustomException('保存热点失败');
        }

        return json_response();
    }

    public function destroyHotspots(Request $request, Production $production, ProductionMedia $production_media)
    {
        $production_media->production_media_hotspots()->delete();

        return json_response();
    }
}
