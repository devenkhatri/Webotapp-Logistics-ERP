<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodSettlementItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(CodSettlement::class, 'cod_settlement_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}

