<?php

namespace App\Repository;

use App\Entity\Stage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;


/**
 * @extends ServiceEntityRepository<Stage>
 *
 * @method Stage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stage[]    findAll()
 * @method Stage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stage::class);
    }

    public function findAllStages(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.apprenant', 'a') // Jointure avec l'entité Apprenant
            ->addSelect('a') // Sélectionnez également l'entité Apprenant
            ->leftJoin('s.eval_entreprise', 'e')
            ->addSelect('e')
            ->leftJoin('s.soutenance', 'sou')
            ->addSelect('sou')
            ->leftJoin('s.rapport', 'rap')
            ->addSelect('rap')
            ->leftJoin('s.tuteur_isen', 'ti')
            ->addSelect('ti')
            ->getQuery()
            ->getResult();
    }
    public function addStage(Stage $stage): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($stage);
        $entityManager->flush();
    }
    public function findByApprenantNom(string $nom)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.apprenant', 'a')
            ->andWhere('a.id = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getResult();
    }
//    /**
//     * @return Stage[] Returns an array of Stage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Stage
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
