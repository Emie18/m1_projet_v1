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
            ->leftJoin('s.groupe', 'gr')
            ->addSelect('gr')
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
    public function findByFilters($nom,$groupe, $annee, $etat, $tuteurIsen)
{
    $queryBuilder = $this->createQueryBuilder('s')
        ->leftJoin('s.apprenant', 'a') // Jointure avec l'entité Apprenant
        ->addSelect('a') // Sélectionnez également l'entité Apprenant
        ->leftJoin('s.eval_entreprise', 'e')
        ->addSelect('e')
        ->leftJoin('s.soutenance', 'sou')
        ->addSelect('sou')
        ->leftJoin('s.rapport', 'rap')
        ->addSelect('rap')
        ->leftJoin('s.groupe', 'gr')
        ->addSelect('gr')
        ->leftJoin('s.tuteur_isen', 'ti')
        ->addSelect('ti')
        ->addSelect('SUBSTRING(s.date_debut, 1, 4) AS HIDDEN adebut')
        ->addSelect('SUBSTRING(s.date_fin, 1, 4) AS HIDDEN afin');

    if ($nom !== "") {
         $queryBuilder
            ->andWhere('a.id = :nom')
            ->setParameter('nom', $nom);
    }

    if ($groupe !== "") {
        $queryBuilder
            ->andWhere('gr.id = :groupe')
            ->setParameter('groupe', $groupe);
    }
   if ($annee !== "") {
        $queryBuilder
        ->having('adebut = :annee OR afin = :annee')
        ->setParameter('annee', $annee);
    }

    if ($etat !== "") {
        if ($etat == '1') {
            $queryBuilder
                ->andWhere('sou.id = :soutenance AND rap.id = :rapport AND e.id = :eval_entreprise'                    )
                ->setParameter('soutenance', '1')
                ->setParameter('rapport', '1')
                ->setParameter('eval_entreprise', '1');
        } elseif ($etat == '2') {
            $queryBuilder
                ->andWhere('sou.id = :soutenance OR rap.id = :rapport OR e.id = :eval_entreprise'                    )
                ->setParameter('soutenance', '2')
                ->setParameter('rapport', '2')
                ->setParameter('eval_entreprise', '2');
        }
    }

    if ($tuteurIsen!="") {
        $queryBuilder
            ->andWhere('ti.id = :tuteurIsen')
            ->setParameter('tuteurIsen', $tuteurIsen);
    }

    return $queryBuilder->getQuery()->getResult();
}

}
