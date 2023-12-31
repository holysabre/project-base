<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use App\Handlers\ImageUploadHandler;
use App\Handlers\QiniuHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Qiniu\Auth as QiniuAuth;

class ImagesController extends Controller
{
    public function store(ImageRequest $request, ImageUploadHandler $uploader, Image $image)
    {
        $user = auth('api')->user();

        $size = $request->type == 'avatar' ? 416 : 1024;
        $result = $uploader->save($request->image, Str::plural($request->type), $user->id, $size);

        $image->path = $result['path'];
        $image->type = $request->type;
        $image->user_id = $user->id;
        $image->save();

        return json_response(200, '', ['image' => new ImageResource($image)]);
    }

    public function qiniuToken(QiniuHandler $qiniuHandler)
    {
        $token = $qiniuHandler->qiniuToken();

        return json_response(200, '', $token);
    }
}
