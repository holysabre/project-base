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

class MediaController extends Controller
{
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
}
