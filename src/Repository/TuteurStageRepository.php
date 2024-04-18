<?php

namespace App\Repository;

use App\Entity\TuteurStage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TuteurStage>
 *
 * @method TuteurStage|null find($id, $lockMode = null, $lockVersion = null)
 * @method TuteurStage|null findOneBy(array $criteria, array $orderBy = null)
 * @method TuteurStage[]    findAll()
 * @method TuteurStage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TuteurStageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TuteurStage::class);
    }
    /**
     * ajouter un tuteur de stage  
     *
     * @param  TuteurStage $tuteur le tuteur à ajouter
     * @return void
     */    
    public function addTuteurStage(TuteurStage $tuteur){
        $entityManager = $this->getEntityManager();
        $entityManager->persist($tuteur);
        $entityManager->flush();
    }
    /**
     * Obtenir tout les tuteur de stage et les classer par ordre alphabétique 
     * @return array: liste des apprenants
     */    
    public function findAllTuteurStage()
    {
        return $this->createQueryBuilder('ts')
            ->orderBy('ts.nom', 'ASC')
            ->getQuery()
            ->getResult();
    
    }
    /**
     * autocompletion du nom pour le back
     * @param String $val le nom à completer
     * @return Array les noms trouvés 
     */    
    public function autoCompleteNom($val){
        $query = $this->createQueryBuilder("t");
        $result = $query->where(
            $query->expr()->like("t.nom", ":nom")
        )
        ->setParameter("nom", "%".$val."%")
        ->orderBy("t.nom", "ASC")
        ->getQuery()
        ->getResult();
        return $result;
    }
    /**
     * Autocompletion du prénom pour le back
     * @param String $var le prénom à compléter
     * @return Array les prénoms trouvés
     */
    public function autoCompletePrenom($val){
        $query = $this->createQueryBuilder("t");
        $result = $query->where(
            $query->expr()->like("t.prenom", ":prenom")
        )
        ->setParameter("prenom", "%".$val."%")
        ->orderBy("t.prenom", "ASC")
        ->groupBy("t.prenom")
        ->getQuery()
        ->getResult();
        return $result;
    }
    /**
     * Rechercher dans la base les correspondances avec le nom ET le prénom
     *
     * @param  String $nom le nom à chercher
     * @param  String $prenom le prénom à chercher
     * @return Array les correspondances trouvées
     */    
    public function findByNom($nom, $prenom){
        $query = $this->createQueryBuilder("t");
        if($nom){
            $result = $query->where(
                $query->expr()->like("t.nom", ":nom")
                
            )
            ->setParameter("nom", "%".$nom."%");
        }if($nom && $prenom){
            $result = $query->andWhere(
                $query->expr()->like("t.prenom", ":prenom")
            )
            ->setParameter("prenom", "%".$prenom."%");
        }elseif($prenom){
            $result = $query->where(
                $query->expr()->like("t.prenom", ":prenom")
            )
            ->setParameter("prenom", "%".$prenom."%");
        }
        $result = $query->orderBy("t.nom", "ASC")
        ->getQuery()
        ->getResult();
        return $result;
    }
//    /**
//     * @return TuteurStage[] Returns an array of TuteurStage objects
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

//    public function findOneBySomeField($value): ?TuteurStage
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
