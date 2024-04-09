<?php

namespace App\Repository;

use App\Entity\Entreprise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entreprise>
 *
 * @method Entreprise|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entreprise|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entreprise[]    findAll()
 * @method Entreprise[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EntrepriseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entreprise::class);
    }
    public function addEntreprise(Entreprise $entreprise){
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entreprise);
        $entityManager->flush();
    } 
       public function findAllEntreprise()
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function autoCompleteNom($val){
        $query = $this->createQueryBuilder("e");
        $result = $query->where(
            $query->expr()->like("e.nom", ":nom")
        )
        ->setParameter("nom", "%".$val."%")
        ->orderBy("e.nom", "ASC")
        ->getQuery()
        ->getResult();
        return $result;
    }

}
