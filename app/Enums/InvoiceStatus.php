<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case EMISE = 'EMISE';
    case PARTIELLEMENT_PAYEE = 'PARTIELLEMENT_PAYEE';
    case PAYEE = 'PAYEE';
    case ANNULEE = 'ANNULEE';

    public function label(): string
    {
        return match ($this) {
            self::EMISE => 'Émise (Non payée)',
            self::PARTIELLEMENT_PAYEE => 'Partiellement payée',
            self::PAYEE => 'Soldée / Payée',
            self::ANNULEE => 'Annulée',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::EMISE => 'bg-amber-50 text-amber-800 border-amber-300',
            self::PARTIELLEMENT_PAYEE => 'bg-blue-50 text-blue-800 border-blue-300',
            self::PAYEE => 'bg-emerald-50 text-emerald-800 border-emerald-300',
            self::ANNULEE => 'bg-rose-50 text-rose-800 border-rose-300',
        };
    }
}
