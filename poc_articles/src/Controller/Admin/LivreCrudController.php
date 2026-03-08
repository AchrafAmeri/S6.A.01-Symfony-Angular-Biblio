<?php

namespace App\Controller\Admin;

use App\Entity\Livre;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_BIBLIO')]
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
            
            TextField::new('langue', 'Langue')
                ->setFormTypeOptions([
                    'attr' => [
                        'list' => 'liste-langues',
                        'autocomplete' => 'off'
                    ]
                ]),
            
            UrlField::new('photoCouverture', 'URL de la couverture')
                ->onlyOnForms()
                ->setRequired(false),
            
            ImageField::new('photoCouverture', 'Couverture')
                ->hideOnForm()
                ->setBasePath(''), 
            
            AssociationField::new('auteurs', 'Auteurs')
                ->setFormTypeOption('by_reference', false),
            AssociationField::new('categories', 'Catégories')
                ->setFormTypeOption('by_reference', false),
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