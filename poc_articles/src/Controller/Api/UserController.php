<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/user/me', name: 'api_user_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        // On récupère l'utilisateur actuellement connecté via son Token JWT
        $user = $this->getUser();

        // S'il n'y a pas d'utilisateur (token invalide ou absent), Symfony bloque automatiquement
        // Mais on s'assure de renvoyer ses données s'il est bien là
        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }
}