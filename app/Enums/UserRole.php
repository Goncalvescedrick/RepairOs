<?php

namespace App\Enums;

enum UserRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case TECHNICIAN = 'technician';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Propriétaire (Owner)',
            self::ADMIN => 'Administrateur (Admin)',
            self::TECHNICIAN => 'Technicien',
        };
    }

    public function isOwner(): bool
    {
        return $this === self::OWNER;
    }

    public function isAdminOrOwner(): bool
    {
        return in_array($this, [self::OWNER, self::ADMIN]);
    }
}
