<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CaptchaRequest;
use Illuminate\Http\Request;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Support\Facades\Cache;
use  Illuminate\Support\Str;

class CaptchasController extends Controller
{
    public function store(CaptchaRequest $request)
    {
        $key = Str::random(15);
        $cacheKey =  'captcha_' . $key;
        $phone = $request->input('phone');

        $phraseBuilder = new PhraseBuilder(4, '0123456789');
        $builder = new CaptchaBuilder(null, $phraseBuilder);
        $captcha = $builder->build();
        $expiredAt = now()->addMinutes(2);
        Cache::put($cacheKey, ['phone' => $phone, 'code' => $captcha->getPhrase()], $expiredAt);

        $result = [
            'captcha_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
            'captcha_image_content' => $captcha->inline(),
            'test' => 'test6',
        ];

        return json_response(200, '', $result);
    }
}
