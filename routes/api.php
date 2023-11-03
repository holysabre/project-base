<?php

use App\Http\Controllers\Api\AuthorizationsController;
use App\Http\Controllers\Api\CaptchasController;
use App\Http\Controllers\Api\ImagesController;
use App\Http\Controllers\Api\MediaGroupsController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\VerificationCodesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::prefix('v1')->name('v1.')->group(function () {

    Route::middleware('throttle:' . config('api.rate_limits.sign'))->group(function () {
        // 图片验证码
        Route::post('captchas', [CaptchasController::class, 'store'])->name('captchas.store');
    });

    // 短信验证码
    Route::post('verificationCodes', [VerificationCodesController::class, 'store'])->name('verificationCodes.store');

    // 鉴权
    Route::group(['prefix' => 'authorizations'], function () {
        Route::group(['prefix' => 'register'], function () {
            Route::post('pwd', [AuthorizationsController::class, 'registerViaPwd']);
            Route::post('sms', [AuthorizationsController::class, 'registerViaSms'])->name('authorizations.login.sms');
        });
        Route::prefix('login')->group(function () {
            Route::post('pwd', [AuthorizationsController::class, 'loginViaPwd'])->name('authorizations.login.pwd');
            Route::post('sms', [AuthorizationsController::class, 'loginViaSms'])->name('authorizations.login.sms');
        });
    });

    // 登录后可以访问的接口
    Route::middleware('auth:api')->group(function () {
        // 当前登录用户信息
        Route::group(['prefix' => 'user'], function () {
            Route::get('', [UsersController::class, 'me'])->name('user.show');
            Route::put('', [UsersController::class, 'update'])->name('user.update');
        });

        Route::group(['prefix' => 'images'], function () {
            Route::post('', [ImagesController::class, 'store'])->name('user.store');
            Route::get('qiniuToken', [ImagesController::class, 'qiniuToken'])->name('user.qiniu.token');
        });

        Route::group(['prefix' => 'media_groups'], function () {
            Route::get('', [MediaGroupsController::class, 'index'])->name('media_groups.index');
            Route::post('', [MediaGroupsController::class, 'store'])->name('media_groups.store');
            Route::group(['prefix' => '{media_group}'], function () {
                Route::put('', [MediaGroupsController::class, 'update'])->name('media_groups.update');
                Route::delete('', [MediaGroupsController::class, 'destroy'])->name('media_groups.destroy');
            });
        });

        Route::group(['prefix' => 'medias'], function () {
            Route::post('', [MediaController::class, 'store'])->name('medias.store');
        });
    });
});
