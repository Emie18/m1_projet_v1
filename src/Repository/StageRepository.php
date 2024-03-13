<?php

namespace App\Repository;

use App\Entity\Stage;
use App\Entity\Etat;
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
            
            ->orderBy('s.id', 'DESC')
            //->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    public function addStage(Stage $stage): bool
    {
        try {
            $entityManager = $this->getEntityManager();
            $entityManager->persist($stage);
            $entityManager->flush();
            return true; // Le stage a été ajouté avec succès
        } catch (\Exception $e) {
            return false; // Une erreur s'est produite lors de l'ajout du stage
        }
    }
    public function updateStage($id, $soutenance, $rapport, $evalEntreprise): bool
    {
        $entityManager = $this->getEntityManager();
        $stage = $this->find($id);
    
        if (!$stage) {
            return false; // Stage introuvable
        }
        
        $stage->setSoutenance($soutenance);
        $stage->setRapport($rapport);
        $stage->setEvalEntreprise($evalEntreprise);
    
        // Enregistrer les modifications
        try {
            $entityManager->flush();
            return true; // Modifications enregistrées avec succès
        } catch (\Exception $e) {
            return false; // Une erreur s'est produite lors de l'enregistrement des modifications
        }
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
    public function findById($id)
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
            ->leftJoin('s.entreprise', 'en')
            ->addSelect('en')
            ->leftJoin('s.tuteur_stage', 'ts')
            ->addSelect('ts')
            ->andWhere('s.id = :id')
            ->setParameter('id', $id)
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
                ->andWhere('(sou.id = :soutenance OR sou.id = :ss) AND (rap.id = :rapport OR rap.id = :ss)  AND (e.id = :eval_entreprise OR e.id = :ss)'                    )
                ->setParameter('soutenance', '1')
                ->setParameter('rapport', '1')
                ->setParameter('ss', '3')
                ->setParameter('eval_entreprise', '1');
        } elseif ($etat == '2') {
            $queryBuilder
                ->andWhere('(sou.id = :soutenance OR sou.id = :ss) OR (rap.id = :rapport OR rap.id = :ss)  OR (e.id = :eval_entreprise OR e.id = :ss)'                    )
                ->setParameter('soutenance', '2')
                ->setParameter('rapport', '2')
                ->setParameter('ss', '4')
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

public function getStatsEntreprise(): array
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select('COUNT(s) as nb_stage')
            ->addSelect('e.nom as entreprise_nom')
            ->from(Stage::class, 's')
            ->leftJoin('s.entreprise', 'e')
            ->groupBy('e.nom')
            ->orderBy('nb_stage', 'DESC')            
            ->setMaxResults(5);
        return $queryBuilder->getQuery()->getResult();
    }

    public function getStatsDay(): array
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
    
        $queryBuilder
            ->select('s')
            ->from(Stage::class, 's');
        $stages = $queryBuilder->getQuery()->getResult();

        $stats = [];
        foreach ($stages as $stage) {
            $dateDebut = $stage->getDateDebut();
            $dateFin = $stage->getDateFin();
            $difference = $dateFin->diff($dateDebut);
            $difference_jours = $difference->days; // Nombre de jours
            $entreprise_nom = $stage->getEntreprise()->getNom(); // Nom de l'entreprise
            
            if (!isset($stats[$entreprise_nom])) {
                $stats[$entreprise_nom] = [
                    'nb_stage' => 1,
                    'total_jours_stage' => $difference_jours
                ];
            } else {
                $stats[$entreprise_nom]['nb_stage']++;
                $stats[$entreprise_nom]['total_jours_stage'] += $difference_jours;
            }
        }
    
        // Tri des statistiques par nombre de stages décroissant
        usort($stats, function($a, $b) {
            return $b['nb_stage'] - $a['nb_stage'];
        });
    
        return $stats;
    }
    public function getStatsMonth(): array
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
    
        $queryBuilder
            ->select('s')
            ->from(Stage::class, 's');
        $stages = $queryBuilder->getQuery()->getResult();
    
        $stats = [];
        $totalStages = count($stages); // Nombre total de stages
    
        foreach ($stages as $stage) {
            $dateDebut = $stage->getDateDebut();
            $dateFin = $stage->getDateFin();
            $difference = $dateDebut->diff($dateFin);
            $difference_mois = ceil($difference->days / 30); // Nombre de mois (approximatif)
            
            // Vérifie si la clé pour le nombre de mois existe déjà dans le tableau $stats
            if (!isset($stats[$difference_mois])) {
                // Si non, crée une nouvelle entrée avec le nombre de mois comme clé et initialise le nombre de stages à 1
                $stats[$difference_mois] = 1;
            } else {
                // Si oui, incrémente simplement le nombre de stages pour ce nombre de mois
                $stats[$difference_mois]++;
            }
        }
    
        // Transformation des données pour le format demandé, en incluant le pourcentage
        $formattedStats = [];
        foreach ($stats as $nb_mois => $nb_stage) {
            // Calcul du pourcentage de stages pour ce nombre de mois
            $pourcentage = ($nb_stage / $totalStages) * 100;
    
            $formattedStats[] = [
                'nb_mois' => $nb_mois . ' mois',
                'nb_stage' => $nb_stage,
                'pourcentage' => round($pourcentage, 2),
            ];
        }
    
        return $formattedStats;
    }

    /*************************trier****************************** */
    public function trierstage(string $colonne, int $ascendant, $apprenant, $tuteur,$annee,$groupe): array
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->leftJoin('s.apprenant', 'a')
            ->addSelect('a')
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

        if ($apprenant!="") {
            $queryBuilder->andWhere('a.id = :nom')->setParameter('nom', $apprenant);
        }
        if ($tuteur!="") {
            $queryBuilder->andWhere('ti.id = :id')->setParameter('id', $tuteur);
        }
        if ($annee !== "") {
            $queryBuilder
            ->having('adebut = :annee OR afin = :annee')
            ->setParameter('annee', $annee);
        }
        if ($groupe!="") {
            $queryBuilder->andWhere('gr.id = :id')->setParameter('id', $groupe);
        }
        switch ($colonne) {
            case 'apprenant':
                if($ascendant==2){
                    $queryBuilder->orderBy('a.nom','ASC');
                }elseif($ascendant==1){
                    $queryBuilder->orderBy('a.nom','DESC');
                }
                break;
            case 'date':
                if($ascendant==2){
                    $queryBuilder->orderBy('s.date_debut','ASC');
                }elseif($ascendant==1){
                    $queryBuilder->orderBy('s.date_debut','DESC');
                }
                break;
            case 'titre':
                if($ascendant==2){
                    $queryBuilder->orderBy('s.titre','ASC');
                }elseif($ascendant==1){
                    $queryBuilder->orderBy('s.titre','DESC');
                }
                break;
            case 'tuteur':
                if($ascendant==2){
                    $queryBuilder->orderBy('ti.nom','ASC');
                }elseif($ascendant==1){
                    $queryBuilder->orderBy('ti.nom','DESC');
                }
                break;
            case 'soutenance':
                if($ascendant==2){
                    $queryBuilder->orderBy('sou.id','ASC');
                }elseif($ascendant==1){
                    $queryBuilder->orderBy('sou.id','DESC');
                }
                break;
            case 'rapport':
                if($ascendant==2){
                    $queryBuilder->orderBy('rap.id','ASC');
                }elseif($ascendant==1){
                    $queryBuilder->orderBy('rap.id','DESC');
                }
                break;
            case 'eval':
                if($ascendant==2){
                    $queryBuilder->orderBy('e.id','ASC');
                }elseif($ascendant==1){
                    $queryBuilder->orderBy('e.id','DESC');
                }
                break;
            default:
                $queryBuilder->orderBy('s.id', 'DESC');
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }


}
