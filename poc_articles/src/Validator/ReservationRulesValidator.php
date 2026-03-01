<?php

namespace App\Validator;

use App\Entity\Reservations;
use App\Entity\Emprunt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReservationRulesValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        /* @var Reservations $value */
        if (!$value instanceof Reservations || !$value->getUtilisateur() || !$value->getLivre()) {
            return;
        }

        $resaRepo = $this->entityManager->getRepository(Reservations::class);
        $empruntRepo = $this->entityManager->getRepository(Emprunt::class);
        $resaId = $value->getId() ?? 0;

        // RÈGLE 1 : L'adhérent a-t-il déjà 3 réservations ? 
        $resasUser = $resaRepo->createQueryBuilder('r')
            ->where('r.utilisateur = :user')
            ->andWhere('r.id != :id')
            ->setParameter('user', $value->getUtilisateur())
            ->setParameter('id', $resaId)
            ->getQuery()
            ->getResult();

        if (count($resasUser) >= 3) {
            $this->context->buildViolation($constraint->messageMaxResa)
                ->atPath('utilisateur')
                ->addViolation();
        }

        // RÈGLE 2 : Le livre est-il déjà réservé ? 
        $resasLivre = $resaRepo->createQueryBuilder('r')
            ->where('r.livre = :livre')
            ->andWhere('r.id != :id')
            ->setParameter('livre', $value->getLivre())
            ->setParameter('id', $resaId)
            ->getQuery()
            ->getResult();

        if (count($resasLivre) > 0) {
            $this->context->buildViolation($constraint->messageDejaReserve)
                ->atPath('livre')
                ->addViolation();
        }

        // RÈGLE 3 : Le livre est-il actuellement emprunté ? 
        $empruntsLivre = $empruntRepo->createQueryBuilder('e')
            ->where('e.livre = :livre')
            ->andWhere('e.dateRetour IS NULL') // Emprunt en cours
            ->setParameter('livre', $value->getLivre())
            ->getQuery()
            ->getResult();

        if (count($empruntsLivre) > 0) {
            $this->context->buildViolation($constraint->messageDejaEmprunte)
                ->atPath('livre')
                ->addViolation();
        }
    }
}