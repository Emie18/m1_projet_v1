<?php

namespace App\Repository;

use App\Entity\Apprenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Apprenant>
 *
 * @method Apprenant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Apprenant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Apprenant[]    findAll()
 * @method Apprenant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApprenantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Apprenant::class);
    }
    public function findAllApprenants(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function addApprenant(Apprenant $apprenant): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($apprenant);
        $entityManager->flush();
    }

}
