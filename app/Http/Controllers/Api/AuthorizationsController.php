<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginViaPwdRequest;
use App\Http\Requests\Api\RegisterViaPwdRequest;
use App\Http\Requests\Api\RegisterViaSmsRequest;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthorizationsController extends Controller
{
    public function registerViaPwd(RegisterViaPwdRequest $request)
    {
        $captchaCacheKey =  'captcha_' . $request->captcha_key;
        $captchaData = Cache::get($captchaCacheKey);

        if (!$captchaData) {
            abort(403, '图片验证码已失效');
        }

        if (!hash_equals($captchaData['code'], $request->captcha_code)) {
            // 验证错误就清除缓存
            Cache::forget($captchaCacheKey);
            throw new AuthenticationException('验证码错误');
        }

        $user = new User();
        $user->name = $request->phone;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->save();

        // 清除图片验证码缓存
        Cache::forget($captchaCacheKey);

        return json_response(200);
    }

    public function registerViaSms(RegisterViaSmsRequest $request)
    {
        $cacheKey = 'verificationCode_' . $request->verification_key;
        $verifyData = Cache::get($cacheKey);

        if (!$verifyData) {
            abort(403, '验证码已失效');
        }

        if (!hash_equals($verifyData['code'], $request->verification_code)) {
            // 返回401
            throw new AuthenticationException('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => $request->password,
        ]);

        // 清除验证码缓存
        Cache::forget($cacheKey);

        return json_response(200);
    }

    public function loginViaPwd(LoginViaPwdRequest $request)
    {
        $credentials['phone'] = $request->phone;
        $credentials['password'] = $request->password;

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            throw new AuthenticationException('用户名或密码错误');
        }

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    protected function respondWithToken($token)
    {
        return json_response(200, '', [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
