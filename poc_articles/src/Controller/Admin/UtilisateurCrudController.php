<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('email'),
            TextField::new('nom'),
            TextField::new('prenom', 'Prénom'),
            
            // Le mot de passe n'est requis qu'à la création
            TextField::new('password', 'Mot de passe')
                ->onlyOnForms()
                ->setRequired($pageName === Crud::PAGE_NEW),
                
            ChoiceField::new('roles')
                ->setChoices([
                    'Adhérent' => 'ROLE_USER',
                    'Bibliothécaire' => 'ROLE_BIBLIO',
                    'Administrateur' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
                
            // Ajout du bouton pour suspendre un adhérent (nécessite un booléen 'isActif' dans l'entité)
            // BooleanField::new('isActif', 'Compte Actif'), 
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Utilisateur && $entityInstance->getPassword()) {
            $entityInstance->setPassword(
                $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword())
            );
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Utilisateur && !str_starts_with($entityInstance->getPassword(), '$argon')) {
            $entityInstance->setPassword(
                $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword())
            );
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}