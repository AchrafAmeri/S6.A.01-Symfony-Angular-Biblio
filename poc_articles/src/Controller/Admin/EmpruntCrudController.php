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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

#[IsGranted('ROLE_BIBLIO')]
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
            TextField::new('retardAffichage', 'En retard ?')
                ->formatValue(function ($value) {
                    if ($value === 'Oui') {
                        return '<span class="badge bg-danger px-2 py-1">Oui</span>';
                    }
                    return '<span class="badge bg-success px-2 py-1">Non</span>';
                })
                ->renderAsHtml()
                ->hideOnForm(),
        ];
    }

    // 1. Ajouter le bouton "Enregistrer le retour"
    public function configureActions(Actions $actions): Actions
    {
        $rendreLivre = Action::new('rendreLivre', 'Enregistrer le retour', 'fas fa-undo')
            ->linkToCrudAction('rendreLivre')
            ->setCssClass('text-success')
            // N'affiche le bouton QUE si la date de retour est vide
            ->displayIf(static function (Emprunt $emprunt) {
                return $emprunt->getDateRetour() === null;
            });
        
        $exportPdf = Action::new('exportPdf', 'Fiche PDF', 'fas fa-file-pdf')
            ->linkToCrudAction('genererPdf')
            ->setCssClass('text-danger');

        return $actions
            ->add(Crud::PAGE_INDEX, $rendreLivre)
            ->add(Crud::PAGE_DETAIL, $rendreLivre)
            ->add(Crud::PAGE_INDEX, $exportPdf)
            ->add(Crud::PAGE_DETAIL, $exportPdf);
    }

    // 2. Logique pour valider le retour
    public function rendreLivre(
        AdminContext $context,
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator
    ): Response {
        // Récupérer l'ID depuis l'URL (comme vu précédemment pour éviter l'erreur de contexte)
        $id = $context->getRequest()->query->get('entityId');
        $emprunt = $entityManager->getRepository(Emprunt::class)->find($id);

        if (!$emprunt) {
            $this->addFlash('danger', 'Emprunt introuvable.');
        } elseif ($emprunt->getDateRetour() !== null) {
            $this->addFlash('warning', 'Ce livre a déjà été retourné.');
        } else {
            // Assigner la date et heure actuelles
            $emprunt->setDateRetour(new \DateTime());
            
            // Pas besoin de persist, l'entité existe déjà, on fait juste un flush
            $entityManager->flush();

            $this->addFlash('success', 'Le retour du livre a bien été enregistré.');
        }

        // Rediriger vers la liste des emprunts
        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function genererPdf(AdminContext $context, EntityManagerInterface $entityManager, Environment $twig): Response
    {
        // 1. Récupérer l'emprunt sélectionné
        $id = $context->getRequest()->query->get('entityId');
        $emprunt = $entityManager->getRepository(Emprunt::class)->find($id);

        if (!$emprunt) {
            $this->addFlash('danger', 'Emprunt introuvable.');
            return $this->redirect($context->getReferrer());
        }

        // 2. Configurer Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        // 3. Générer le HTML depuis notre template Twig
        $html = $twig->render('admin/fiche_emprunt.html.twig', [
            'emprunt' => $emprunt
        ]);

        // 4. Intégrer le HTML et créer le PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // --- NOUVEAUTÉ : GÉNÉRATION DU NOM DE FICHIER PRÉCIS ---
        $nom = $emprunt->getUtilisateur()->getNom();
        $titreLivre = $emprunt->getLivre()->getTitre();
        $dateStr = $emprunt->getDateEmprunt()->format('d-m-Y');

        // On nettoie le titre du livre et le nom pour éviter les bugs liés aux espaces ou accents dans le nom de fichier
        $titrePropre = preg_replace('/[^A-Za-z0-9\-]/', '_', $titreLivre);
        $nomPropre = preg_replace('/[^A-Za-z0-9\-]/', '_', $nom);
        
        // Ex: Emprunt_Dupont_Le_Seigneur_Des_Anneaux_07-03-2026.pdf
        $fileName = sprintf('Emprunt_%s_%s_%s.pdf', $nomPropre, substr($titrePropre, 0, 25), $dateStr);
        // --------------------------------------------------------

        // 5. Renvoyer le PDF en téléchargement au navigateur
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
            ]
        );
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
            $resaRepo = $entityManager->getRepository(Reservations::class);
            $reservation = $resaRepo->findOneBy([
                'livre' => $livre,
                'utilisateur' => $utilisateur
            ]);

            if ($reservation) {
                $entityManager->remove($reservation);
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}