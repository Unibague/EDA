<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{

    protected $guarded = [];

    public function commitment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Commitment::class);
    }

    public function formAnswers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FormAnswers::class);
    }


    use HasFactory;
}
