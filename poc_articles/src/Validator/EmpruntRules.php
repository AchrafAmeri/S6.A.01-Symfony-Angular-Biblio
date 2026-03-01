<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EmpruntRules extends Constraint
{
    public string $messageMaxEmprunts = 'Cet adhérent a déjà atteint la limite de 5 emprunts en cours.';
    public string $messageLivreIndisponible = 'Ce livre est déjà emprunté par un autre adhérent.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}