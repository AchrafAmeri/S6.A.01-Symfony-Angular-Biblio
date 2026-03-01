<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ReservationRules extends Constraint
{
    public string $messageMaxResa = 'Cet adhérent a déjà atteint la limite de 3 réservations simultanées.';
    public string $messageDejaReserve = 'Ce livre est déjà réservé par un autre adhérent.';
    public string $messageDejaEmprunte = 'Ce livre est actuellement emprunté, il ne peut pas être réservé.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}