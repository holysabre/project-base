<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Image;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function me(Request $request)
    {
        $user = auth('api')->user();

        return json_response(200, '', ['user' => (new UserResource($user))->showSensitiveFields()]);
    }

    public function update(UserRequest $request)
    {
        $user = auth('api')->user();

        $attributes = $request->only(['name', 'email', 'introduction']);

        if ($request->avatar_image_id) {
            $image = Image::find($request->avatar_image_id);

            $attributes['avatar'] = $image->path;
        }

        $user->update($attributes);

        return json_response(200);
    }
}
