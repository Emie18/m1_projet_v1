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
    /**
     * Obtenir tout les apprenants et les classer par ordre alphabétique 
     * @return array: liste des apprenants
     */
    public function findAllApprenants(): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * ajouter un apprenant  
     *
     * @param  Apprenant $apprenant
     * @return void
     */
    public function addApprenant(Apprenant $apprenant): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($apprenant);
        $entityManager->flush();
    }
        
    /**
     * autoComplete
     *
     * @param  String $val nom a tester pour l'automplétion
     * @return Array les correspondances trouvées
     */
    public function autoComplete($val){
        $query = $this->createQueryBuilder("a");
        $result = $query->where(
            $query->expr()->like("a.nom", ":nom")
        )
        ->orWhere(
            $query->expr()->like("a.prenom", ":nom")
        )
        ->setParameter("nom", "%".$val."%")
        ->orderBy("a.nom", "ASC")
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
        $query = $this->createQueryBuilder("a");
        $result = $query->where(
            $query->expr()->like("a.nom", ":nom")
        )
        ->setParameter("nom", "%".$val."%")
        ->orderBy("a.nom", "ASC")
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
        $query = $this->createQueryBuilder("a");
        $result = $query->where(
            $query->expr()->like("a.prenom", ":prenom")
        )
        ->setParameter("prenom", "%".$val."%")
        ->orderBy("a.prenom", "ASC")
        ->groupBy("a.prenom")
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
        $query = $this->createQueryBuilder("a");
        if($nom){
            $result = $query->where(
                $query->expr()->like("a.nom", ":nom")
                
            )
            ->setParameter("nom", "%".$nom."%");
        }if($nom && $prenom){
            $result = $query->andWhere(
                $query->expr()->like("a.prenom", ":prenom")
            )
            ->setParameter("prenom", "%".$prenom."%");
        }elseif($prenom){
            $result = $query->where(
                $query->expr()->like("a.prenom", ":prenom")
            )
            ->setParameter("prenom", "%".$prenom."%");
        }
        $result = $query->orderBy("a.nom", "ASC")
        ->getQuery()
        ->getResult();
        return $result;
    }
}
