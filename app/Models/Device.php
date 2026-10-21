<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'category',
        'brand',
        'model',
        'serial_number',
        'aesthetic_condition',
        'accessories',
        'notes',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function repairTickets(): HasMany
    {
        return $this->hasMany(RepairTicket::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match (strtolower((string) $this->category)) {
            'laptop' => 'Ordinateur portable',
            'desktop' => 'Ordinateur de bureau / Fixe',
            'printer' => 'Imprimante',
            'network' => 'Équipement réseau',
            default => 'Autre équipement',
        };
    }

    public function getDeviceCategoryLabelAttribute(): string
    {
        return $this->category_label;
    }
}
