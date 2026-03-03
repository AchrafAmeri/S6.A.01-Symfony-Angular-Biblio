<?php

namespace App\Controller\Admin;

use App\Entity\Emprunt;
use App\Entity\Reservations;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Doctrine\ORM\EntityManagerInterface;

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
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setHelp('Laissez vide pour mettre la date et l\'heure actuelles.'),
            DateTimeField::new('dateRetour', 'Date de retour')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setRequired(false),
            BooleanField::new('isEnRetard', 'En retard ?')
                ->renderAsSwitch(false)
                ->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Emprunt) {
            $livre = $entityInstance->getLivre();
            $utilisateur = $entityInstance->getUtilisateur();
            
            // 1. Automatiser la date d'emprunt si elle est vide
            if ($entityInstance->getDateEmprunt() === null) {
                $entityInstance->setDateEmprunt(new \DateTime());
            }

            // 2. Vérifier que le livre n'est pas déjà emprunté
            if ($livre->getIsEmprunte()) {
                throw new \LogicException('Impossible : Ce livre est déjà emprunté.');
            }

            // 3. Vérifier le quota de 5 livres maximum 
            $empruntsEnCours = 0;
            foreach ($utilisateur->getEmprunts() as $emp) {
                if ($emp->getDateRetour() === null) {
                    $empruntsEnCours++;
                }
            }
            if ($empruntsEnCours >= 5) {
                throw new \LogicException('Impossible : L\'adhérent a atteint son quota de 5 emprunts.');
            }

            // 4. TRANSFORMATION EN EMPRUNT 
            // On cherche si cet utilisateur avait réservé ce livre précis
            $resaRepo = $entityManager->getRepository(Reservations::class);
            $reservation = $resaRepo->findOneBy([
                'livre' => $livre,
                'utilisateur' => $utilisateur
            ]);

            // Si une réservation existe, on la supprime
            if ($reservation) {
                $entityManager->remove($reservation);
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}