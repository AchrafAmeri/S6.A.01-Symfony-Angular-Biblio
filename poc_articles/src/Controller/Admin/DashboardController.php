<?php

namespace App\Controller\Admin;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Reservations;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function index(): Response
    {
        // Calcul des statistiques demandées par le projet
        $nbLivres = $this->em->getRepository(Livre::class)->count([]);
        $nbAdherents = $this->em->getRepository(Utilisateur::class)->count([]);
        $nbCategories = $this->em->getRepository(Categorie::class)->count([]);
        
        // Un emprunt est "en cours" si la date de retour est nulle
        $nbEmpruntsEnCours = $this->em->getRepository(Emprunt::class)->count(['dateRetour' => null]);

        return $this->render('admin/dashboard.html.twig', [
            'nbLivres' => $nbLivres,
            'nbAdherents' => $nbAdherents,
            'nbEmpruntsEnCours' => $nbEmpruntsEnCours,
            'nbCategories' => $nbCategories,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Gestion Bibliothèque');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Catalogue');
        yield MenuItem::linkToCrud('Livres', 'fas fa-book', Livre::class);
        yield MenuItem::linkToCrud('Auteurs', 'fas fa-pen-nib', Auteur::class);
        yield MenuItem::linkToCrud('Catégories', 'fas fa-tags', Categorie::class);

        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Emprunts', 'fas fa-hand-holding', Emprunt::class);
        yield MenuItem::linkToCrud('Réservations', 'fas fa-calendar-check', Reservations::class);
        yield MenuItem::linkToCrud('Adhérents', 'fas fa-users', Utilisateur::class)->setPermission('ROLE_ADMIN');    }
}