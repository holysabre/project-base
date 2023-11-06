<?php

namespace App\Models\Traits;

use Illuminate\Support\Carbon;

trait TimestampFormat
{
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateTimeString();
    }
}
