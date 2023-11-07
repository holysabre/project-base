<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionHotspot extends Model
{
    use HasFactory;

    protected $fillable = ['production_id', 'name', 'style', 'ath', 'atv', 'linkedsence'];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }
}
