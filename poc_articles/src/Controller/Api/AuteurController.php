<?php

namespace App\Controller\Api;

use App\Entity\Auteur;
use App\Repository\AuteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class AuteurController extends AbstractController
{
    #[Route('/auteurs', name: 'api_auteurs', methods: ['GET'])]
    public function index(AuteurRepository $auteurRepository): JsonResponse
    {
        return $this->json($auteurRepository->findAll(), 200, [], ['groups' => 'auteur:read']);
    }

    #[Route('/auteurs/{id}', name: 'api_auteurs_show', methods: ['GET'])]
    public function show(Auteur $auteur): JsonResponse
    {
        return $this->json($auteur, 200, [], ['groups' => 'auteur:read']);
    }
}