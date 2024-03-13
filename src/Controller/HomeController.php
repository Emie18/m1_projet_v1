<?php

namespace App\Controller;

use App\Entity\Apprenant;
use App\Entity\Entreprise;
use App\Entity\Etat;
use App\Entity\Groupe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Repository\StageRepository;
use App\Repository\GroupeRepository;
use App\Repository\TuteurIsenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\AjoutstageType;
use App\Form\TuteurIsenType;
use App\Entity\Stage;
use App\Entity\TuteurIsen;
use App\Entity\TuteurStage;
use App\Repository\ApprenantRepository;
use App\Repository\EtatRepository;
use PHPUnit\TextUI\XmlConfiguration\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Importer la classe EntityType
use Symfony\Component\HttpFoundation\Session\SessionInterface;



class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, StageRepository $stageRepository, GroupeRepository $groupRepository, ApprenantRepository $apprenantRepository, TuteurIsenRepository $tuteurIsenRepository, EtatRepository $etatRepository): Response
    {
        $stages = $stageRepository->findAllStages();
        $noms = $apprenantRepository->findAllApprenants();
        $groupes = $groupRepository->findAll();

        // Nombre d'éléments par page
        //$itemsPerPage = 50;

        // // Récupérer le numéro de la page actuelle depuis la requête
        // $currentPage = $request->query->getInt('page', 1);

        // // Calculer l'offset pour récupérer les éléments correspondant à la page actuelle
        // $offset = ($currentPage - 1) * $itemsPerPage;
        // $stagesPagines = array_slice($stages, $offset, $itemsPerPage);

        // $totalStages = count($stages);
        // $totalPages = ceil($totalStages / $itemsPerPage);

        $etats_stages = [['id' => 1, 'libelle' => 'Terminé'], ['id' => 2, 'libelle' => 'En cours']];
        $annees = [];
        foreach ($stages as $stage) {
            $anneeDebut = $stage->getDateDebut()->format('Y');
            $anneeFin = $stage->getDateFin()->format('Y');
            if (!in_array($anneeDebut, $annees)) {
                $annees[] = $anneeDebut;
            }
            if (!in_array($anneeFin, $annees)) {
                $annees[] = $anneeFin;
            }
        }
        $professeurs = $tuteurIsenRepository->findAllTuteurIsens();

        return $this->render('home/index.html.twig', [
            'stages' => $stages, // Utilisation des stages paginés
            'stages_form' => $stages, // Utilisation des stages paginés
            'noms' => $noms,
            'groupes' => $groupes,
            'etats_stages' => $etats_stages,
            'annees' => $annees,
            'professeurs' => $professeurs,
            // 'CurrentPage' =>$currentPage,
            // 'totalPages' =>$totalPages,

        ]);
    }
    #[Route('/filtrage', name: 'app_filtrage')]

    public function index2(Request $request, StageRepository $stageRepository) {
        // Récupération des paramètres de requête
        $nom2 = $request->query->get('nom');
        $groupe = $request->query->get('groupe');
        $annee = $request->query->get('annee');
        $etat = $request->query->get('etat_stage');
        $professeur = $request->query->get('professeur');
        // Récupération des stages filtrés en fonction des paramètres
        $stages = $stageRepository->findByFilters($nom2,$groupe,$annee, $etat, $professeur);

        // $itemsPerPage = 50;

        // // Récupérer le numéro de la page actuelle depuis la requête
        // $currentPage = $request->query->getInt('page', 1);

        // // Calculer l'offset pour récupérer les éléments correspondant à la page actuelle
        // $offset = ($currentPage - 1) * $itemsPerPage;
        // $stagesPagines = array_slice($stages, $offset, $itemsPerPage);

        // $totalStages = count($stages);
        // $totalPages = ceil($totalStages / $itemsPerPage);
        // $filters = compact('nom2', 'groupe', 'annee', 'etat', 'professeur');


        // Rendu d'une vue partielle contenant uniquement les données de la table
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
            // 'CurrentPage' =>$currentPage,
            // 'totalPages' =>$totalPages,
            // 'filters'=>$filters,
        ]);
    }
    #[Route('/fichedetail', name: 'app_fichedetail')]

    public function fichedetail(Request $request, StageRepository $stageRepository) {
        $id = $request->query->get('id');
        $stage = $stageRepository->findByID($id);

        $dateDebut = $stage[0]->getDateDebut();
        $dateFin = $stage[0]->getDateFin();
        $difference = $dateFin->diff($dateDebut);
        $difference_mois = $difference->m; // Nombre de mois
        return $this->render('home/fiche_detail.html.twig', [
            'stage' => $stage,
            'nbmois'=> $difference_mois,
            
        ]);
    }

    #[Route('/statistique', name: 'app_statistique')]
    public function statistique(StageRepository $stageRepository): Response
    {
        $statistics = $stageRepository->getStatsEntreprise();
        // Création d'un nouveau tableau avec les pourcentages et les noms des entreprises
        $statisticsWithPercentage = [];

        // Calculer le total des stagiaires
        $totalStagiaires = array_sum(array_column($statistics, 'nb_stage'));

        // Calculer le pourcentage pour chaque entreprise et ajouter au tableau
        foreach ($statistics as $stat) {
            $pourcentage = ($stat['nb_stage'] / $totalStagiaires) * 100;
            $pourcentageArrondi = round($pourcentage, 2); // Arrondi à 2 décimales

            // Ajouter les données au nouveau tableau
            $statisticsWithPercentage[] = [
                'entreprise' => $stat['entreprise_nom'],
                'pourcentage' => $pourcentageArrondi
            ];
        }
        $statisticsDay = $stageRepository->getStatsMonth();


        return $this->render('statistique/statistique.html.twig', [
            'statistics' => $statistics,
            'pourcentage' => $statisticsWithPercentage,
            'statMois' => $statisticsDay
        ]);
    }
/*-------------------trier-----------------------------------*/

    #[Route('/trier/{type}', name: 'trier')]
    public function trier(Request $request, StageRepository $stageRepository, $type)
    {
        $des = $request->query->get('desc');
        $tuteur = $request->query->get('tuteur');
        $apprenant = $request->query->get('apprenant');
        $annee = $request->query->get('annee');
        $groupe = $request->query->get('groupe');

        $stages = $stageRepository->trierstage($type, $des, $apprenant, $tuteur, $annee,$groupe);
        // // Récupérer le numéro de la page actuelle depuis la requête
        // $currentPage = $request->query->getInt('page', 1);
        // $itemsPerPage = 50;
        // // Calculer l'offset pour récupérer les éléments correspondant à la page actuelle
        // $offset = ($currentPage - 1) * $itemsPerPage;
        // $stagesPagines = array_slice($stages, $offset, $itemsPerPage);

        // $totalStages = count($stages);
        // $totalPages = ceil($totalStages / $itemsPerPage);
        // $sortingParams = compact('type', 'des', 'apprenant', 'tuteur', 'annee', 'groupe');

        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
            // 'sortingParams'=>$sortingParams,
            // 'CurrentPage'=> $currentPage,
            // 'totalPages'=>$totalPages,
        ]);
    }
    #[Route('/autocomplete', name: 'app_auto')]
    public function autoComplete(Request $request, ApprenantRepository $apprenantRepository,
    GroupeRepository $groupeRepository, TuteurIsenRepository $tuteurIsenRepository){
        $filtre = $request->query->get("filtre");
        $val = $request->query->get("val");
        $result = [];
        switch($filtre){
            case "inputNom": 
                // $result = $apprenantRepository->autoComplete($val);
                $result = $apprenantRepository->autoComplete($val);
                foreach ($result as $apprenant) {
                    $formattedResult[] = [
                        'id' => $apprenant->getId(),
                        'nom' => $apprenant->getNom(),
                        'prenom' => $apprenant->getPrenom()
                    ];
                }
                break;
            case "inputGroupe":
                $result = $groupeRepository->autoComplete($val);
                foreach ($result as $groupe) {
                    $formattedResult[] = [
                        'id' => $groupe->getId(),
                        'libelle' => $groupe->getLibelle(),
                    ];
                }
                break;
            case "inputProf":
                $result = $tuteurIsenRepository->autoComplete($val);
                foreach ($result as $tuteur) {
                    $formattedResult[] = [
                        'id' => $tuteur->getId(),
                        'nom' => $tuteur->getNom(),
                        'prenom' => $tuteur->getPrenom()
                    ];
                }
                break;
                
        }
        $result = array_filter($result);
        $jsonData = json_encode($result);
        if ($jsonData === false) {
            // Afficher le message d'erreur
            echo "Erreur d'encodage JSON : " . json_last_error_msg();
        }
        $jsonData = json_encode($formattedResult,JSON_UNESCAPED_UNICODE);
        $response = new JsonResponse($jsonData, 200, [], true);
    
        return $response;
    }


}
