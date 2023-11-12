<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionMediaHotspot extends Model
{
    use HasFactory;

    protected $fillable = ['production_media_id', 'name', 'style', 'ath', 'atv', 'linkedsence', 'uuid'];

    public function production_media()
    {
        return $this->belongsTo(ProductionMedia::class);
    }
}
