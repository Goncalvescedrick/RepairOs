<?php

namespace App\Models;

use App\Enums\RepairStatus;
use App\Enums\TicketPriority;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairTicket extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'reference',
        'customer_id',
        'device_id',
        'assigned_to_user_id',
        'status',
        'priority',
        'reported_issue',
        'intake_condition',
        'accessories_received',
        'deposit_date',
        'estimated_completion_date',
        'closed_at',
        'internal_notes',
        'client_notes',
        'public_tracking_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => RepairStatus::class,
            'priority' => TicketPriority::class,
            'deposit_date' => 'datetime',
            'estimated_completion_date' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(RepairStatusHistory::class)->orderByDesc('created_at');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(RepairIntervention::class)->orderByDesc('created_at');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
