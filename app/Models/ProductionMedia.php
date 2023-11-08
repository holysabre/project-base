<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionMedia extends Model
{
    use HasFactory;

    protected $fillable = ['production_id', 'media_id', 'is_main', 'sort'];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function production_media_hotspots()
    {
        return $this->hasMany(ProductionMediaHotspot::class);
    }
}
