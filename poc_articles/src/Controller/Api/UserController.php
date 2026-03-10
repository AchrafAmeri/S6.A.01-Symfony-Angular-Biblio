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
    public function updateMe(Request $request, EntityManagerInterface $em, \App\Repository\UtilisateurRepository $userRepo): JsonResponse
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Non authentifié.'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['error' => 'Données JSON invalides.'], 400);
        }

        // --- NOUVEAU : GESTION DE L'EMAIL ---
        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            // On vérifie si un autre utilisateur possède déjà cet email
            $existingUser = $userRepo->findOneBy(['email' => $data['email']]);
            
            if ($existingUser) {
                // On renvoie une erreur 409 (Conflit) pour qu'Angular sache exactement ce qui cloche
                return $this->json(['error' => 'Cet email est déjà utilisé par un autre compte.'], 409);
            }
            
            $user->setEmail($data['email']);
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