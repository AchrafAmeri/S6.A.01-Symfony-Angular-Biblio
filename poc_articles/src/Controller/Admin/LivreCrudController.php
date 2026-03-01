<?php

namespace App\Controller\Admin;

use App\Entity\Livre;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LivreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Livre::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('titre', 'Titre du livre'),
            DateField::new('dateSortie', 'Date de parution'),
            TextField::new('langue', 'Langue'),
            
            // Affichage de l'image (compatible avec tes URL Picsum actuelles)
            ImageField::new('photoCouverture', 'Couverture')
                ->setBasePath('') 
                ->setUploadDir('public/uploads/couvertures') // Préparation si tu fais le bonus d'upload
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
            
            // Gestion des relations ManyToMany
            AssociationField::new('auteurs', 'Auteurs')
                ->setFormTypeOption('by_reference', false),
            AssociationField::new('categories', 'Catégories')
                ->setFormTypeOption('by_reference', false),
        ];
    }
}