<?php

namespace App\Controller\Api;

use App\Entity\Livre;
use App\Entity\Reservations;
use App\Repository\EmpruntRepository;
use App\Repository\ReservationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/reservations')]
class ReservationsController extends AbstractController
{
    #[Route('/{id}', name: 'api_reservation_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Livre $livre, 
        EntityManagerInterface $em, 
        ReservationsRepository $resRepo,
        EmpruntRepository $empRepo
    ): JsonResponse {
        $user = $this->getUser();

        // 1. Règle : 3 réservations max
        $activeReservations = $resRepo->findBy(['utilisateur' => $user]);
        if (count($activeReservations) >= 3) {
            return $this->json(['message' => 'Limite de 3 réservations atteinte.'], 409);
        }

        // 2. Règle : Livre déjà réservé ?
        $isReserved = $resRepo->findOneBy(['livre' => $livre]);
        if ($isReserved) {
            return $this->json(['message' => 'Ce livre est déjà réservé.'], 409);
        }

        // 3. Règle : Livre actuellement emprunté ?
        $isBorrowed = $empRepo->findOneBy(['livre' => $livre, 'dateRetour' => null]);
        if ($isBorrowed) {
            return $this->json(['message' => 'Ce livre est actuellement emprunté.'], 409);
        }

        // Création de la réservation
        $reservation = new Reservations();
        $reservation->setLivre($livre);
        $reservation->setUtilisateur($user);
        $reservation->setDateResa(new \DateTimeImmutable());

        $em->persist($reservation);
        $em->flush();

        return $this->json(['message' => 'Livre réservé avec succès !'], 201);
    }

    // Liste des réservations de l'adhérent connecté
    #[Route('', name: 'api_mes_reservations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(ReservationsRepository $resRepo): JsonResponse
    {
        $user = $this->getUser();
        $reservations = $resRepo->findBy(['utilisateur' => $user]);
        
        return $this->json($reservations, 200, [], ['groups' => 'user:read']);
    }

    // Annulation d'une réservation
    #[Route('/{id}', name: 'api_reservation_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Reservations $reservation, EntityManagerInterface $em): JsonResponse
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            return $this->json(['message' => 'Action non autorisée.'], 403);
        }

        $em->remove($reservation);
        $em->flush();

        return $this->json(['message' => 'Réservation annulée.'], 200);
    }
}