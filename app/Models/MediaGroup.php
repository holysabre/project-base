<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sort', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWithUserId($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }
}
