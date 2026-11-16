<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateSlab extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function rateCard(): BelongsTo
    {
        return $this->belongsTo(RateCard::class);
    }
}

