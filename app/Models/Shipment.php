<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'actual_weight' => 'decimal:2',
        'volumetric_weight' => 'decimal:2',
        'chargeable_weight' => 'decimal:2',
        'declared_value' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'base_freight' => 'decimal:2',
        'fuel_surcharge' => 'decimal:2',
        'handling_fee' => 'decimal:2',
        'cod_fee' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'gst_tax' => 'decimal:2',
        'total_freight' => 'decimal:2',
        'otp_verified_at' => 'datetime',
        'delivered_at' => 'datetime',
        'estimated_delivery_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function originHub(): BelongsTo
    {
        return $this->belongsTo(Hub::class, 'origin_hub_id');
    }

    public function destinationHub(): BelongsTo
    {
        return $this->belongsTo(Hub::class, 'destination_hub_id');
    }

    public function currentHub(): BelongsTo
    {
        return $this->belongsTo(Hub::class, 'current_hub_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pickupAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pickup_agent_id');
    }

    public function deliveryAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_agent_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ShipmentPackage::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(ShipmentTrackingEvent::class)->orderBy('id', 'desc');
    }

    public function deliveryProof(): HasOne
    {
        return $this->hasOne(DeliveryProof::class);
    }

    public function deliveryAttempts(): HasMany
    {
        return $this->hasMany(DeliveryAttempt::class);
    }

    public function codCollection(): HasOne
    {
        return $this->hasOne(CodCollection::class);
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isCod(): bool
    {
        return $this->payment_type === 'cod';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'booked' => 'bg-blue-100 text-blue-800 border-blue-200',
            'pickup_scheduled' => 'bg-amber-100 text-amber-800 border-amber-200',
            'picked_up' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'received_at_hub', 'sorted', 'bagged' => 'bg-purple-100 text-purple-800 border-purple-200',
            'in_transit', 'dispatched' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            'out_for_delivery' => 'bg-orange-100 text-orange-800 border-orange-200',
            'delivered' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'failed_attempt' => 'bg-rose-100 text-rose-800 border-rose-200',
            'rto_initiated', 'rto_in_transit', 'rto_delivered' => 'bg-red-100 text-red-800 border-red-200',
            'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }
}
