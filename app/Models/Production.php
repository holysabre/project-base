<?php

namespace App\Models;

use App\Models\Traits\TimestampFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Production extends Model
{
    use HasFactory, SoftDeletes, TimestampFormat;

    const TYPE1 = 1;
    const TYPE2 = 2;

    public static $mapType = [
        self::TYPE1 => '全景图',
        self::TYPE2 => '全景视频',
    ];

    protected $fillable = ['production_group_id', 'media_id', 'title', 'type', 'description', 'thumb', 'lng', 'lat', 'sort', 'address', 'status'];

    public $appends = ['created_at', 'updated_at'];

    public $hidden = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function production_group()
    {
        return $this->belongsTo(ProductionGroup::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function production_media()
    {
        return $this->hasMany(ProductionMedia::class);
    }

    public function scopeWithUserId($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }
}
