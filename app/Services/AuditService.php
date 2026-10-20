<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditService
{
    protected array $blacklistedKeys = [
        'password',
        'password_confirmation',
        'token',
        'secret',
        'remember_token',
        'public_tracking_token',
    ];

    public function log(
        string $event,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
        ?int $tenantId = null
    ): AuditLog {
        $tenantId = $tenantId ?? app(TenantManager::class)->getTenantId();
        $userId = $userId ?? auth()->id();

        return AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event' => $event,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 500),
            'old_values' => $this->sanitizeValues($oldValues),
            'new_values' => $this->sanitizeValues($newValues),
            'created_at' => now(),
        ]);
    }

    protected function sanitizeValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $cleaned = [];
        foreach ($values as $key => $value) {
            if (in_array(strtolower($key), $this->blacklistedKeys, true)) {
                $cleaned[$key] = '[CONFIDENTIEL]';
            } elseif (is_array($value)) {
                $cleaned[$key] = $this->sanitizeValues($value);
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }
}
