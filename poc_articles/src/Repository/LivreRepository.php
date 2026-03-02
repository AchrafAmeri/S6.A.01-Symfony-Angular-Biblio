<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Livre>
 */
class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    /**
     * Recherche avancée et pagination des livres
     */
    public function findBySearchCriteria(?string $titre, ?string $auteur, ?string $categorie, int $page = 1, int $limit = 9): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.auteurs', 'a')
            ->addSelect('a')
            ->leftJoin('l.categories', 'c')
            ->addSelect('c');

        if (!empty($titre)) {
            $qb->andWhere('l.titre LIKE :titre')
               ->setParameter('titre', '%' . $titre . '%');
        }

        if (!empty($auteur)) {
            $qb->andWhere('a.nom LIKE :auteur OR a.prenom LIKE :auteur')
               ->setParameter('auteur', '%' . $auteur . '%');
        }

        if (!empty($categorie)) {
            $qb->andWhere('c.nom LIKE :categorie')
               ->setParameter('categorie', '%' . $categorie . '%');
        }

        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        $paginator = new Paginator($qb, true);
        
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        // On convertit l'itérateur en tableau de manière sûre
        return [
            'data' => iterator_to_array($paginator->getIterator()),
            'totalPages' => $totalPages > 0 ? (int)$totalPages : 1,
            'currentPage' => $page
        ];
    }

    //    /**
    //     * @return Livre[] Returns an array of Livre objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Livre
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
