<?php

namespace App\Controller\Api;

use App\Repository\LivreRepository;
use App\Repository\EmpruntRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/api/admin/stats')]
#[IsGranted('ROLE_ADMIN')]
class StatsController extends AbstractController
{
    #[Route('', name: 'api_admin_stats', methods: ['GET'])]
    public function getStats(
        LivreRepository $livreRepo, 
        EmpruntRepository $empRepo,
        UtilisateurRepository $adhRepo
    ): JsonResponse {
        // 1. Totaux simples
        $totalLivres = $livreRepo->count([]);
        $totalAdherents = $adhRepo->count([]);
        $empruntsEnCours = $empRepo->count(['dateRetour' => null]);

        // 2. Statistiques par mois (Ex: Nombre d'emprunts par mois sur l'année)
        // On utilise une requête personnalisée via le QueryBuilder
        $empruntsParMois = $empRepo->createQueryBuilder('e')
            ->select("SUBSTRING(e.dateEmprunt, 1, 7) as mois, COUNT(e.id) as total")
            ->groupBy('mois')
            ->orderBy('mois', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();

        return $this->json([
            'totals' => [
                'livres' => $totalLivres,
                'adherents' => $totalAdherents,
                'empruntsEnCours' => $empruntsEnCours
            ],
            'history' => $empruntsParMois
        ]);
    }
}