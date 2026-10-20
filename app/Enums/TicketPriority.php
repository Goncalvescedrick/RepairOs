<?php

namespace App\Enums;

enum TicketPriority: string
{
    case BASSE = 'BASSE';
    case NORMALE = 'NORMALE';
    case HAUTE = 'HAUTE';
    case URGENTE = 'URGENTE';

    public function label(): string
    {
        return match ($this) {
            self::BASSE => 'Basse',
            self::NORMALE => 'Normale',
            self::HAUTE => 'Haute',
            self::URGENTE => 'Urgente',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::BASSE => 'bg-slate-100 text-slate-700 border-slate-300',
            self::NORMALE => 'bg-blue-50 text-blue-700 border-blue-200',
            self::HAUTE => 'bg-amber-100 text-amber-800 border-amber-300',
            self::URGENTE => 'bg-red-100 text-red-800 border-red-300 animate-pulse',
        };
    }
}
