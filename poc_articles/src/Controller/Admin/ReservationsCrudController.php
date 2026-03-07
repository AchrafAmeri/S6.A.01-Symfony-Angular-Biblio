<?php

namespace App\Controller\Admin;

use App\Entity\Emprunt;
use App\Entity\Reservations;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_BIBLIO')]
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
            AssociationField::new('utilisateur', 'Adhérent'),
            AssociationField::new('livre', 'Livre réservé'),
            DateTimeField::new('dateResa', 'Date de réservation')
                ->setFormat('dd/MM/yyyy HH:mm'),
        ];
    }

    // 1. Ajouter le bouton d'action dans l'interface
    public function configureActions(Actions $actions): Actions
    {
        $transformerAction = Action::new('transformerEnEmprunt', 'Transformer en emprunt', 'fas fa-exchange-alt')
            ->linkToCrudAction('transformerEnEmprunt') // Fait le lien avec la méthode ci-dessous
            ->setCssClass('text-success'); // Met le bouton en vert pour bien le distinguer

        return $actions
            ->add(Crud::PAGE_INDEX, $transformerAction)
            ->add(Crud::PAGE_DETAIL, $transformerAction);
    }

    // 2. La logique de transformation
    public function transformerEnEmprunt(
        AdminContext $context, 
        EntityManagerInterface $entityManager, 
        AdminUrlGenerator $adminUrlGenerator,
        ValidatorInterface $validator
    ): Response {
        // 1. On récupère l'ID passé dans l'URL par EasyAdmin
        $id = $context->getRequest()->query->get('entityId');
        
        // 2. On charge la réservation manuellement via le repository
        $reservation = $entityManager->getRepository(Reservations::class)->find($id);

        // Au cas où la réservation a été supprimée entre temps
        if (!$reservation) {
            $this->addFlash('danger', 'Réservation introuvable.');
        } else {
            // Étape A : Créer l'emprunt avec la date/heure actuelle
            $emprunt = new Emprunt();
            $emprunt->setUtilisateur($reservation->getUtilisateur());
            $emprunt->setLivre($reservation->getLivre());
            $emprunt->setDateEmprunt(new \DateTime()); 

            // Étape B : Vérifier les règles de validation de l'Emprunt
            $erreurs = $validator->validate($emprunt);

            if (count($erreurs) > 0) {
                // Si une règle bloque (ex: quota dépassé), on affiche l'erreur en rouge
                $this->addFlash('danger', $erreurs[0]->getMessage());
            } else {
                // Étape C : Si tout est valide, on sauvegarde l'emprunt et on détruit la réservation
                $entityManager->persist($emprunt);
                $entityManager->remove($reservation);
                $entityManager->flush();

                $this->addFlash('success', 'La réservation a bien été transformée en emprunt actif.');
            }
        }

        // Étape D : Rediriger le bibliothécaire vers la liste des réservations
        $url = $adminUrlGenerator
            ->setController(ReservationsCrudController::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}