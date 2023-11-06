<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    const TYPE1 = 1;
    const TYPE2 = 2;

    public static $mapType = [
        self::TYPE1 => '全景图',
        self::TYPE2 => '全景视频',
    ];
}
