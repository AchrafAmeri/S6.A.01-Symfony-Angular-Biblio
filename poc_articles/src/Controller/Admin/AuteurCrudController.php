<?php

namespace App\Controller\Admin;

use App\Entity\Auteur;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

#[IsGranted('ROLE_BIBLIO')]
class AuteurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Auteur::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('prenom', 'Prénom'),
            TextField::new('nom', 'Nom'),
            
            UrlField::new('photo', 'URL de la photo')
                ->onlyOnForms()
                ->setRequired(false),
            
            ImageField::new('photo', 'Photo')
                ->hideOnForm()
                ->setBasePath(''),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Permet au bibliothécaire de cliquer pour voir les détails d'un livre
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            
            // On verrouille les actions de modification pour l'admin uniquement
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }
}
