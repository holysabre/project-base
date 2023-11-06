<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MediaRequest;
use App\Jobs\SliceImage;
use App\Models\Image;
use App\Models\Media;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(array_keys(Media::$mapType))],
            'media_group_id' => 'sometimes',
            'keywords' => 'sometimes',
        ]);

        $user_id = auth('api')->id();

        $builder = Media::query()->with([
            'thumb_image:id,path'
        ])->withUserId($user_id)
            ->where('type', $request->type);

        if (!empty($request->media_group_id)) {
            $builder->where('media_group_id', $request->media_group_id);
        }

        if (!empty($request->keywords)) {
            $builder->where('name', 'like', '%' . $request->keywords . '%');
        }

        $list = $builder->paginate($request->input('per_page', 10));

        return json_response(200, '', ['list' => $list->items()], $list->total());
    }

    public function store(MediaRequest $request)
    {
        $user_id = auth('api')->id();

        DB::beginTransaction();
        try {
            $image = new Image();
            $image->user_id = $user_id;
            $image->type = 'panorama';
            $image->path = $request->path;
            $image->source = 'qiniu';
            $image->save();

            $media = new Media();
            $media->fill($request->all());
            $media->name = getFilenameByPath($request->path);
            $media->user_id = $user_id;
            $media->panorama_image_id = $image->id;
            $media->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e->getFile(), $e->getLine()]);
            throw new CustomException('上传素材失败');
        }

        dispatch(new SliceImage($media));

        return json_response(200);
    }

    public function update(MediaRequest $request, Media $media)
    {
        $media->fill($request->all());
        $media->save();

        return json_response();
    }

    public function destroy(Request $request, Media $media)
    {
        $media->delete();

        return json_response();
    }
}
