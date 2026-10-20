<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case ESPECES = 'ESPECES';
    case MOBILE_MONEY = 'MOBILE_MONEY';
    case VIREMENT = 'VIREMENT';
    case CARTE = 'CARTE';
    case AUTRE = 'AUTRE';

    public function label(): string
    {
        return match ($this) {
            self::ESPECES => 'Espèces (Cash)',
            self::MOBILE_MONEY => 'Mobile Money (Orange / Moov / Wave)',
            self::VIREMENT => 'Virement bancaire',
            self::CARTE => 'Carte bancaire',
            self::AUTRE => 'Autre moyen',
        };
    }
}
