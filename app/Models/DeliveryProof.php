<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryProof extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_agent_id');
    }
}
