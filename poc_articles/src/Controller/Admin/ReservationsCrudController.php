<?php

namespace App\Controller\Admin;

use App\Entity\Reservations;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class ReservationsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservations::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            // Associe l'adhérent à la réservation [cite: 49, 52]
            AssociationField::new('utilisateur', 'Adhérent'),
            // Associe le livre à la réservation [cite: 42, 49]
            AssociationField::new('livre', 'Livre réservé'),
            // Date de la réservation (DateTimeImmutable dans l'entité) [cite: 51]
            DateTimeField::new('dateResa', 'Date de réservation')
                ->setFormat('dd/MM/yyyy HH:mm'),
        ];
    }
}
