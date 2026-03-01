<?php

namespace App\Controller\Admin;

use App\Entity\Emprunt;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class EmpruntCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Emprunt::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            
            AssociationField::new('utilisateur', 'Adhérent'),
            
            AssociationField::new('livre', 'Livre emprunté'),
            
            DateTimeField::new('dateEmprunt', 'Date d\'emprunt')
                ->setFormat('dd/MM/yyyy HH:mm'),
                
            DateTimeField::new('dateRetour', 'Date de retour')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setRequired(false)
                ->setHelp('Laissez vide si le livre n\'est pas encore rendu.'),
        ];
    }
}