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
use Illuminate\Support\Facades\Crypt;

class RepairTicket extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

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
        'intake_checklist',
        'accessories_received',
        'deposit_date',
        'estimated_completion_date',
        'closed_at',
        'warranty_days',
        'warranty_expires_at',
        'internal_notes',
        'device_access_code_encrypted',
        'client_notes',
        'public_tracking_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => RepairStatus::class,
            'priority' => TicketPriority::class,
            'intake_checklist' => 'array',
            'deposit_date' => 'datetime',
            'estimated_completion_date' => 'datetime',
            'closed_at' => 'datetime',
            'warranty_days' => 'integer',
            'warranty_expires_at' => 'datetime',
        ];
    }

    public function setAccessCode(?string $plainCode): void
    {
        if (empty(trim((string) $plainCode))) {
            $this->device_access_code_encrypted = null;
        } else {
            $this->device_access_code_encrypted = Crypt::encryptString(trim($plainCode));
        }
    }

    public function getDecryptedAccessCodeAttribute(): ?string
    {
        if (empty($this->device_access_code_encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->device_access_code_encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public function isUnderWarranty(): bool
    {
        return $this->warranty_expires_at !== null && $this->warranty_expires_at->isFuture();
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
