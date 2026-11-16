<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostalCode extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ServiceZone::class, 'zone_id');
    }
}

