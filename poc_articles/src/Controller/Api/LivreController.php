<?php

namespace App\Controller\Api;

use App\Entity\Livre;
use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class LivreController extends AbstractController
{
    #[Route('/livres', name: 'api_livres', methods: ['GET'])]
    public function index(Request $request, LivreRepository $livreRepository): JsonResponse
    {
        $titre = $request->query->get('titre', '');
        $auteur = $request->query->get('auteur', '');
        $categorie = $request->query->get('categorie', '');
        
        $page = $request->query->getInt('page', 1);

        $livres = $livreRepository->findBySearchCriteria($titre, $auteur, $categorie, $page);

        return $this->json($livres, 200, [], ['groups' => 'livre:read']);
    }

    #[Route('/livres/{id}', name: 'api_livres_show', methods: ['GET'])]
    public function show(Livre $livre): JsonResponse
    {
        return $this->json($livre, 200, [], ['groups' => 'livre:read']);
    }
}