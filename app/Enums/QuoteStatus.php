<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case BROUILLON = 'BROUILLON';
    case ENVOYE = 'ENVOYE';
    case ACCEPTE = 'ACCEPTE';
    case REFUSE = 'REFUSE';
    case EXPIRE = 'EXPIRE';
    case ANNULE = 'ANNULE';

    public function label(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::ENVOYE => 'Envoyé au client',
            self::ACCEPTE => 'Accepté',
            self::REFUSE => 'Refusé',
            self::EXPIRE => 'Expiré',
            self::ANNULE => 'Annulé',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::BROUILLON => 'bg-slate-100 text-slate-700 border-slate-300',
            self::ENVOYE => 'bg-blue-50 text-blue-800 border-blue-200',
            self::ACCEPTE => 'bg-emerald-50 text-emerald-800 border-emerald-300',
            self::REFUSE => 'bg-rose-50 text-rose-800 border-rose-200',
            self::EXPIRE => 'bg-amber-50 text-amber-800 border-amber-200',
            self::ANNULE => 'bg-slate-200 text-slate-600 border-slate-300',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::BROUILLON;
    }
}
