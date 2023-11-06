<?php

namespace App\Models;

use App\Models\Traits\TimestampFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, SoftDeletes, TimestampFormat;

    const TYPE1 = 1;
    const TYPE2 = 2;

    public static $mapType = [
        self::TYPE1 => '全景图',
        self::TYPE2 => '全景视频',
    ];

    protected $fillable = ['media_group_id', 'name', 'type', 'dist_path', 'thumb', 'lng', 'lat'];

    public $appends = ['created_at', 'updated_at'];

    public $hidden = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media_group()
    {
        return $this->belongsTo(MediaGroup::class);
    }

    public function panorama_image()
    {
        return $this->belongsTo(Image::class);
    }

    public function thumb_image()
    {
        return $this->belongsTo(Image::class);
    }

    public function scopeWithUserId($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }
}
