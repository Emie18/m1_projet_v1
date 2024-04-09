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
    /**
     * ajouter un tuteur ISEN  
     *
     * @param  TuteurIsen $tuteur le tuteur à ajouter
     * @return void
     */
    public function addTuteurIsen(TuteurIsen $tuteur): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($tuteur);
        $entityManager->flush();
    }
    /**
     * Obtenir tout les tuteur ISEN et les classer par ordre alphabétique 
     * @return array: liste des apprenants
     */    public function findAllTuteurIsens()
    {
        return $this->createQueryBuilder('ti')
            ->orderBy('ti.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /**
     * autoComplete
     *
     * @param  String $val nom a tester pour l'automplétion pour le front
     * @return Array les correspondances trouvées
     */    
    public function autoComplete($val){
        $query = $this->createQueryBuilder("t");
        $result = $query->where(
            $query->expr()->like("t.nom", ":nom")
        )
        ->orWhere(
            $query->expr()->like("t.prenom", ":nom")
        )
        ->setParameter("nom", "%".$val."%")
        ->orderBy("t.nom", "ASC")
        ->getQuery()
        ->getResult();
        
        return $result;
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
        ->groupBy("t.nom")
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
