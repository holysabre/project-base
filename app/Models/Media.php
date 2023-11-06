<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Media extends Model
{
    use HasFactory;

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

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateTimeString();
    }
}
