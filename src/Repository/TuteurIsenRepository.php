<?php

namespace App\Repository;

use App\Entity\TuteurIsen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TuteurIsen>
 *
 * @method TuteurIsen|null find($id, $lockMode = null, $lockVersion = null)
 * @method TuteurIsen|null findOneBy(array $criteria, array $orderBy = null)
 * @method TuteurIsen[]    findAll()
 * @method TuteurIsen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TuteurIsenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TuteurIsen::class);
    }
    public function addTuteurIsen(TuteurIsen $tuteur): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($tuteur);
        $entityManager->flush();
    }
    public function findAllTuteurIsens()
    {
        return $this->createQueryBuilder('ti')
            ->orderBy('ti.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return TuteurIsen[] Returns an array of TuteurIsen objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TuteurIsen
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
