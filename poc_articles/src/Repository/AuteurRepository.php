<?php

namespace App\Repository;

use App\Entity\Auteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Auteur>
 */
class AuteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Auteur::class);
    }

    /**
     * Recherche avancée et pagination des auteurs
     */
    public function findBySearchCriteria(?string $nom, int $page = 1, int $limit = 9): array
    {
        $qb = $this->createQueryBuilder('a');

        if (!empty($nom)) {
            $qb->andWhere('a.nom LIKE :nom OR a.prenom LIKE :nom')
               ->setParameter('nom', '%' . $nom . '%');
        }

        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        $paginator = new Paginator($qb, true);

        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        return [
            'data' => iterator_to_array($paginator->getIterator()),
            'totalPages' => $totalPages > 0 ? (int)$totalPages : 1,
            'currentPage' => $page
        ];
    }

    //    /**
    //     * @return Auteur[] Returns an array of Auteur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Auteur
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
