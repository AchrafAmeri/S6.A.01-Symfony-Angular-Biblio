<?php

namespace App\Validator;

use App\Entity\Emprunt;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmpruntRulesValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        /* @var Emprunt $value */
        if (!$value instanceof Emprunt || !$value->getUtilisateur() || !$value->getLivre()) {
            return;
        }

        // Si l'emprunt a une date de retour, on ne bloque pas (c'est un historique ou un retour)
        if ($value->getDateRetour() !== null) {
            return;
        }

        $empruntRepo = $this->entityManager->getRepository(Emprunt::class);
        $empruntId = $value->getId() ?? 0; // 0 si c'est un nouvel emprunt

        // RÈGLE 1 : L'adhérent a-t-il déjà 5 emprunts en cours ?
        $empruntsActuelsUser = $empruntRepo->createQueryBuilder('e')
            ->where('e.utilisateur = :user')
            ->andWhere('e.dateRetour IS NULL')
            ->andWhere('e.id != :id') // On exclut l'emprunt actuel si on est en train de le modifier
            ->setParameter('user', $value->getUtilisateur())
            ->setParameter('id', $empruntId)
            ->getQuery()
            ->getResult();

        if (count($empruntsActuelsUser) >= 5) {
            $this->context->buildViolation($constraint->messageMaxEmprunts)
                ->atPath('utilisateur') // Le message s'affichera sous le champ adhérent
                ->addViolation();
        }

        // RÈGLE 2 : Le livre est-il déjà emprunté et non rendu ?
        $empruntsActuelsLivre = $empruntRepo->createQueryBuilder('e')
            ->where('e.livre = :livre')
            ->andWhere('e.dateRetour IS NULL')
            ->andWhere('e.id != :id')
            ->setParameter('livre', $value->getLivre())
            ->setParameter('id', $empruntId)
            ->getQuery()
            ->getResult();

        if (count($empruntsActuelsLivre) > 0) {
            $this->context->buildViolation($constraint->messageLivreIndisponible)
                ->atPath('livre') // Le message s'affichera sous le champ livre
                ->addViolation();
        }
    }
}