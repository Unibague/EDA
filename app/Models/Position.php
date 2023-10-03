<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{

    public function users (): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
    public function responseIdeals (): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ResponseIdeal::class);
    }

    use HasFactory;
}
