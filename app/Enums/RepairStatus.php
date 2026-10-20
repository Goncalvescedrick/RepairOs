<?php

namespace App\Enums;

enum RepairStatus: string
{
    case RECU = 'RECU';
    case DIAGNOSTIC = 'DIAGNOSTIC';
    case DEVIS_EN_ATTENTE = 'DEVIS_EN_ATTENTE';
    case EN_ATTENTE_CLIENT = 'EN_ATTENTE_CLIENT';
    case EN_REPARATION = 'EN_REPARATION';
    case TEST = 'TEST';
    case PRET_A_RETIRER = 'PRET_A_RETIRER';
    case RESTITUE = 'RESTITUE';
    case ANNULE = 'ANNULE';

    public function label(): string
    {
        return match ($this) {
            self::RECU => 'Reçu',
            self::DIAGNOSTIC => 'En diagnostic',
            self::DEVIS_EN_ATTENTE => 'Devis en attente',
            self::EN_ATTENTE_CLIENT => 'En attente accord client',
            self::EN_REPARATION => 'En cours de réparation',
            self::TEST => 'En cours de test',
            self::PRET_A_RETIRER => 'Prêt à être retiré',
            self::RESTITUE => 'Restitué au client',
            self::ANNULE => 'Annulé',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::RECU => 'bg-slate-100 text-slate-800 border-slate-300',
            self::DIAGNOSTIC => 'bg-blue-50 text-blue-800 border-blue-200',
            self::DEVIS_EN_ATTENTE => 'bg-amber-50 text-amber-800 border-amber-200',
            self::EN_ATTENTE_CLIENT => 'bg-orange-50 text-orange-800 border-orange-200',
            self::EN_REPARATION => 'bg-indigo-50 text-indigo-800 border-indigo-200',
            self::TEST => 'bg-purple-50 text-purple-800 border-purple-200',
            self::PRET_A_RETIRER => 'bg-emerald-50 text-emerald-800 border-emerald-300',
            self::RESTITUE => 'bg-green-100 text-green-900 border-green-300',
            self::ANNULE => 'bg-rose-50 text-rose-800 border-rose-200',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::RESTITUE, self::ANNULE]);
    }
}
