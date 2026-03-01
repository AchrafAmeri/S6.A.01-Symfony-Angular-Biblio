<?php

namespace App\Controller\Api;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class UserActionsController extends AbstractController
{
    #[Route('/mes-emprunts', name: 'api_mes_emprunts', methods: ['GET'])]
    public function mesEmprunts(): JsonResponse
    {
        $user = $this->getUser();
        
        // On vérifie que l'utilisateur est bien connecté et qu'il correspond à ton entité
        if (!$user instanceof Utilisateur) {
            return $this->json(['erreur' => 'Utilisateur non trouvé ou non connecté'], 401);
        }
        
        return $this->json($user->getEmprunts(), 200, [], ['groups' => 'user:read']);
    }

    #[Route('/mes-reservations', name: 'api_mes_reservations', methods: ['GET'])]
    public function mesReservations(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof Utilisateur) {
            return $this->json(['erreur' => 'Utilisateur non trouvé ou non connecté'], 401);
        }
        
        return $this->json($user->getReservations(), 200, [], ['groups' => 'user:read']);
    }
}