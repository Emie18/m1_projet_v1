<?php
/**************************************************************************
* Nom du fichier: HomeController.php
* Description: Controller du front
* Auteurs: Emilie Le Rouzic, Thibault Tanné
* Date de création: avril 2024
* Version: 1.0
**************************************************************************/
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Repository\StageRepository;
use App\Repository\GroupeRepository;
use App\Repository\TuteurIsenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Repository\ApprenantRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\EtatRepository;
use App\Repository\TuteurStageRepository;

class HomeController extends AbstractController
{
    //page d'accueil
    #[Route('/', name: 'app_home')]
    public function index(StageRepository $stageRepository, GroupeRepository $groupRepository, 
    ApprenantRepository $apprenantRepository, TuteurIsenRepository $tuteurIsenRepository): Response
    {
        //récupération de tout les stages
        $stages = $stageRepository->findAllStages();
        //récupération de tout les apprenants
        $noms = $apprenantRepository->findAllApprenants();
        //récupération de tout les groupes
        $groupes = $groupRepository->findAll();
        //création de la liste des états d'un stage
        $etats_stages = [['id' => 1, 'libelle' => 'Terminé'], ['id' => 2, 'libelle' => 'En cours']];
        $annees = [];
        //création de la liste contenant les différente années
        foreach ($stages as $stage) {
            //var_dump($stage);
            $anneeDebut = $stage->getDateDebut()->format('Y');
            $anneeFin = $stage->getDateFin()->format('Y');
            if (!in_array($anneeDebut, $annees)) {
                $annees[] = $anneeDebut;
            }
            if (!in_array($anneeFin, $annees)) {
                $annees[] = $anneeFin;
            }
            
        }
        arsort($annees);
        //récupération de tout les tuteurs ISEN
        $professeurs = $tuteurIsenRepository->findAllTuteurIsens();
        //envois des données à la page twig "index.html.twig"
        return $this->render('home/index.html.twig', [
            'stages' => $stages, 
            'stages_form' => $stages,
            'noms' => $noms,
            'groupes' => $groupes,
            'etats_stages' => $etats_stages,
            'annees' => $annees,
            'professeurs' => $professeurs,

        ]);
    }
    //filtrage des stages
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
        // Rendu d'une vue partielle contenant uniquement les données de la table
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
        ]);
    }
    //filtrage pour les autres page du back : apprenant , tuteur Stage , tuteur Isen , Entreprise.
    #[Route("/filtrageSolo", name: "app_filtrageSolo")]
    public function filtreSolo(Request $request, TuteurIsenRepository $tuteurIsenRepository,
    TuteurStageRepository $tuteurStageRepository, ApprenantRepository $apprenantRepository,
    EntrepriseRepository $entrepriseRepository,){
        $element = $request->query->get("part");
        
        switch($element){
            case "apprenant":
                $nom = $request->query->get("nom");
                $prenom = $request->query->get("prenom");
                $result = $apprenantRepository->findByNom($nom, $prenom);
                break;
            case "tuteurStage":
                $nom = $request->query->get("nom");
                $prenom = $request->query->get("prenom");
                $result = $tuteurStageRepository->findByNom($nom, $prenom);
                break;
            case "tuteurIsen":
                $nom = $request->query->get("nom");
                $prenom = $request->query->get("prenom");
                $result = $tuteurIsenRepository->findByNom($nom, $prenom);
                break;
            case "entreprise":
                $nom = $request->query->get("nom");
                $result = $entrepriseRepository->autoCompleteNom($nom);
                return $this->render("back/_table.html.twig",[
                    "entreprises" => $result,
                ]);
                break;
        }
        
        return $this->render("back/_table.html.twig", [
            "personnes" => $result,
        ]);
    }
    //affichage de la fiche détail
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
    //pour la page des statistiques
    #[Route('/statistique', name: 'app_statistique')]
    public function statistique(StageRepository $stageRepository): Response
    {
        $statisticsTuteur = $stageRepository->getStatsTuteurIsen();
        $statisticsEntreprise = $stageRepository->getStatsEntreprise();
        // Création d'un nouveau tableau avec les pourcentages et les noms des entreprises
        $statisticsWithPercentageTuteur = [];
        $statisticsWithPercentageEntreprise = [];

        // Calculer le total des stagiaires
        $totalStagiaires = array_sum(array_column($statisticsTuteur, 'nb_stage'));
       
        // Calculer le pourcentage pour chaque entreprise et ajouter au tableau
        foreach ($statisticsTuteur as $stat) {
            $pourcentage = ($stat['nb_stage'] / $totalStagiaires) * 100;
            $pourcentageArrondi = round($pourcentage, 2); // Arrondi à 2 décimales

            // Ajouter les données au nouveau tableau
            $statisticsWithPercentageTuteur[] = [
                'tuteur' => $stat['tuteur'],
                'pourcentage' => $pourcentageArrondi
            ];
            
        }
        $totalStagiaires = array_sum(array_column($statisticsEntreprise, 'nb_stage'));
       
        foreach($statisticsEntreprise as $stat){
            $pourcentage = ($stat['nb_stage'] / $totalStagiaires) * 100;
            $pourcentageArrondi = round($pourcentage, 2); // Arrondi à 2 décimales

            // Ajouter les données au nouveau tableau
            $statisticsWithPercentageEntreprise[] = [
                'entreprise' => $stat['entreprise_nom'],
                'pourcentage' => $pourcentageArrondi
            ];
        }
        $statisticsDay = $stageRepository->getStatsMonth();
        
        return $this->render('statistique/statistique.html.twig', [
            'statistics' => $statisticsTuteur,
            'pourcentage' => $statisticsWithPercentageTuteur,
            'statEntreprise' => $statisticsEntreprise,
            'pourcentageEntreprise' => $statisticsWithPercentageEntreprise,
            'statMois' => $statisticsDay
        ]);
    }
    //focntion de tri des stages
    #[Route('/trier/{type}', name: 'trier')]
    public function trier(Request $request, StageRepository $stageRepository, $type)
    {
        $des = $request->query->get('desc');
        $tuteur = $request->query->get('tuteur');
        $apprenant = $request->query->get('apprenant');
        $annee = $request->query->get('annee');
        $groupe = $request->query->get('groupe');
        $etat = $request->query->get('etat');

        $stages = $stageRepository->trierstage($type, $des, $apprenant, $tuteur, $annee,$groupe, $etat);

        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
        ]);
    }
    //focntion pour l'autocomplétion
    #[Route('/autocomplete', name: 'app_auto')]
    public function autoComplete(Request $request, ApprenantRepository $apprenantRepository,
    GroupeRepository $groupeRepository, TuteurIsenRepository $tuteurIsenRepository,
    EntrepriseRepository $entrepriseRepository, TuteurStageRepository $tuteurStageRepository){

        $filtre = $request->query->get("filtre");
        $val = $request->query->get("val");
        $result = [];
        $formattedResult =[];
        //rechercher le bon élément en fonction du filtre
        switch($filtre){
            case "inputNom": 
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

            case "nomApprenant":
                $name = $request->query->get("name");
                switch($name){
                    case "nom":
                        $result = $apprenantRepository->autoCompleteNom($val);
                        foreach($result as $apprenant){
                            $formattedResult[] = [
                                'id' => $apprenant->getId(),
                                'nom' => $apprenant->getNom(),
                            ];
                        }
                        break;

                    case "prenom":
                        $result = $apprenantRepository->autoCompletePrenom($val);
                        foreach($result as $apprenant){
                            $formattedResult[] = [
                                'id' => $apprenant->getId(),
                                'prenom' => $apprenant->getPrenom(),
                            ];
                        }
                        break;

                }
            break;

            case "nomTuteurStage":
                $name = $request->query->get("name");
                switch($name){
                    case "nom":
                        $result = $tuteurStageRepository->autoCompleteNom($val);
                        foreach($result as $apprenant){
                            $formattedResult[] = [
                                'id' => $apprenant->getId(),
                                'nom' => $apprenant->getNom(),
                            ];
                        }
                        break;

                    case "prenom":
                        $result = $tuteurStageRepository->autoCompletePrenom($val);
                        foreach($result as $apprenant){
                            $formattedResult[] = [
                                'id' => $apprenant->getId(),
                                'prenom' => $apprenant->getPrenom(),
                            ];
                        }
                        break;
                }
            break;

            case "nomTuteurIsen":
                $name = $request->query->get("name");
                switch($name){
                    case "nom":
                        $result = $tuteurIsenRepository->autoCompleteNom($val);
                        foreach($result as $apprenant){
                            $formattedResult[] = [
                                'id' => $apprenant->getId(),
                                'nom' => $apprenant->getNom(),
                            ];
                        }
                        break;

                    case "prenom":
                        $result = $tuteurIsenRepository->autoCompletePrenom($val);
                        foreach($result as $apprenant){
                            $formattedResult[] = [
                                'id' => $apprenant->getId(),
                                'prenom' => $apprenant->getPrenom(),
                            ];
                        }
                        break;

                }
            break;

            case "nomEntreprise":
                $result = $entrepriseRepository->autoCompleteNom($val);
                foreach($result as $entreprise){
                    $formattedResult[] = [
                        'id' => $entreprise->getId(),
                        'nom' => $entreprise->getNom(),
                    ];
                }
                break;

        }
        $jsonData = json_encode($formattedResult,JSON_UNESCAPED_UNICODE);
        $response = new JsonResponse($jsonData, 200, [], true);
    
        return $response;
    }


}
