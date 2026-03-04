<?php

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/user/me', name: 'api_user_me_update', methods: ['PUT'])]
    public function updateMe(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Non authentifié.'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['error' => 'Données JSON invalides.'], 400);
        }

        if (isset($data['adressePostale'])) {
            $user->setAdressePostale($data['adressePostale']);
        }

        if (isset($data['numTel'])) {
            $user->setNumTel($data['numTel']);
        }

        $em->flush();

        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }
}