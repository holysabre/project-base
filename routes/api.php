<?php

use App\Http\Controllers\Api\AuthorizationsController;
use App\Http\Controllers\Api\CaptchasController;
use App\Http\Controllers\Api\ImagesController;
use App\Http\Controllers\Api\MediaGroupsController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\ProductionGroupsController;
use App\Http\Controllers\Api\ProductionMediaController;
use App\Http\Controllers\Api\ProductionMediaHotspotsController;
use App\Http\Controllers\Api\ProductionsController;
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

        // 鉴权
        Route::group(['prefix' => 'authorizations'], function () {
            Route::post('logout', [AuthorizationsController::class, 'logout'])->name('authorizations.logout');
        });

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
            Route::get('', [MediaController::class, 'index'])->name('media.index');
            Route::post('', [MediaController::class, 'store'])->name('media.store');
            Route::group(['prefix' => '{media}'], function () {
                Route::put('', [MediaController::class, 'update'])->name('media.update');
                Route::delete('', [MediaController::class, 'destroy'])->name('media.destroy');
            });
        });

        Route::group(['prefix' => 'production_groups'], function () {
            Route::get('', [ProductionGroupsController::class, 'index'])->name('production_groups.index');
            Route::post('', [ProductionGroupsController::class, 'store'])->name('production_groups.store');
            Route::group(['prefix' => '{production_group}'], function () {
                Route::put('', [ProductionGroupsController::class, 'update'])->name('production_groups.update');
                Route::delete('', [ProductionGroupsController::class, 'destroy'])->name('production_groups.destroy');
            });
        });

        Route::group(['prefix' => 'productions'], function () {
            Route::get('', [ProductionsController::class, 'index'])->name('productions.index');
            Route::post('', [ProductionsController::class, 'store'])->name('productions.store');
            Route::group(['prefix' => '{production}'], function () {
                Route::get('', [ProductionsController::class, 'show'])->name('productions.show');
                Route::put('', [ProductionsController::class, 'update'])->name('productions.update');
                Route::delete('', [ProductionsController::class, 'destroy'])->name('productions.destroy');
                Route::post('clear_hotspots', [ProductionsController::class, 'clearHotspots'])->name('productions.clear_hotspots');

                Route::group(['prefix' => 'production_media'], function () {
                    Route::get('', [ProductionMediaController::class, 'index'])->name('production_media.index');
                    Route::group(['prefix' => '{production_media}'], function () {
                        Route::get('', [ProductionMediaController::class, 'show'])->name('production_media.show');
                        Route::group(['prefix' => 'hotspots'], function () {
                            Route::put('', [ProductionMediaController::class, 'saveHotspots'])->name('production_media.save_hotspots');
                            Route::delete('', [ProductionMediaController::class, 'destroyHotspots'])->name('production_media.destroy_hotspots');
                        });
                    });
                });
            });
        });
    });
});
