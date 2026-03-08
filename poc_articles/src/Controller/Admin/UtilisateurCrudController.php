<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

#[IsGranted('ROLE_BIBLIO')]
class UtilisateurCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;
    private Security $security;

    // 1. On injecte le service Security pour connaître le rôle de la personne connectée
    public function __construct(UserPasswordHasherInterface $passwordHasher, Security $security)
    {
        $this->passwordHasher = $passwordHasher;
        $this->security = $security;
    }

    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    // 2. On filtre la liste affichée dans le tableau
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Si l'utilisateur connecté n'est PAS un Administrateur
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            // Il ne voit que les utilisateurs qui n'ont ni ROLE_ADMIN ni ROLE_BIBLIO
            $qb->andWhere('entity.roles NOT LIKE :admin_role')
               ->andWhere('entity.roles NOT LIKE :biblio_role')
               ->setParameter('admin_role', '%"ROLE_ADMIN"%')
               ->setParameter('biblio_role', '%"ROLE_BIBLIO"%');
        }

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        $rolesChoices = ['Adhérent' => 'ROLE_USER'];
        
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $rolesChoices['Bibliothécaire'] = 'ROLE_BIBLIO';
            $rolesChoices['Administrateur'] = 'ROLE_ADMIN';
        }

        // 1. On prépare le champ dateAdhesion à part
        $dateAdhesionField = DateField::new('dateAdhesion', 'Date d\'adhésion');
        
        // 2. On pré-remplit la date SEULEMENT sur la page de création
        if ($pageName === Crud::PAGE_NEW) {
            $dateAdhesionField->setFormTypeOption('data', new \DateTime('today'));
        }

        return [
            IdField::new('id')->hideOnForm(),
            
            TextField::new('email', 'Adresse Email'),
            TextField::new('nom', 'Nom'),
            TextField::new('prenom', 'Prénom'),
            
            // 3. On insère notre champ préparé ici
            $dateAdhesionField,
                
            DateField::new('dateNaiss', 'Date de naissance')
                ->hideOnIndex(),
                
            TelephoneField::new('numTel', 'Numéro de téléphone')
                ->hideOnIndex(),
                
            TextField::new('adressePostale', 'Adresse postale')
                ->hideOnIndex(),

            TextField::new('password', 'Mot de passe')
                ->onlyOnForms()
                ->setFormType(PasswordType::class) 
                ->setRequired($pageName === Crud::PAGE_NEW), 
                
            ChoiceField::new('roles', 'Rôle du compte') 
                ->setChoices($rolesChoices)
                ->allowMultipleChoices()
                ->renderExpanded()
                ->setRequired(true),
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
        if ($entityInstance instanceof Utilisateur) {
            
            // Protection anti-hack
            $targetRoles = $entityInstance->getRoles();
            if (!$this->security->isGranted('ROLE_ADMIN') && (in_array('ROLE_ADMIN', $targetRoles) || in_array('ROLE_BIBLIO', $targetRoles))) {
                throw new AccessDeniedException('Vous n\'avez pas les droits pour modifier cet utilisateur.');
            }

            // --- GESTION DU MOT DE PASSE VIDE ---
            $nouveauMotDePasse = $entityInstance->getPassword();

            if ($nouveauMotDePasse) {
                // Si l'admin a tapé un nouveau mot de passe, on le hash
                $entityInstance->setPassword(
                    $this->passwordHasher->hashPassword($entityInstance, $nouveauMotDePasse)
                );
            } else {
                // S'il a laissé le champ vide, on récupère l'ancien hash dans la mémoire de Doctrine
                $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
                $entityInstance->setPassword($originalData['password']);
            }
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
    
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Seul un administrateur peut supprimer un compte.');
        }
        
        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }
}