<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtoRecord extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'delivered_to_merchant_at' => 'datetime',
    ];

    public function originalShipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'original_shipment_id');
    }
}
