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
use App\Form\AjoutstageType;
use App\Form\TuteurIsenType;
use App\Entity\Stage;
use App\Entity\TuteurIsen;
use App\Entity\TuteurStage;
use App\Repository\ApprenantRepository;
use App\Repository\EtatRepository;
use PHPUnit\TextUI\XmlConfiguration\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Importer la classe EntityType


class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, StageRepository $stageRepository, GroupeRepository $groupRepository, ApprenantRepository $apprenantRepository, TuteurIsenRepository $tuteurIsenRepository, EtatRepository $etatRepository): Response
    {
        $stages = $stageRepository->findAllStages();
        $noms = $apprenantRepository->findAllApprenants();
        $groupes = $groupRepository->findAll();
        $etats_stages = $etatRepository->findAll();
        // $etats_stages = [['id'=>1 , 'libelle'=>'Terminé'], ['id'=>2 , 'libelle'=>'En cours'] ];
        $annees = [];
        foreach ($stages as $stage) {
            $anneeDebut = $stage->getDateDebut()->format('Y');
            $anneeFin = $stage->getDateFin()->format('Y');
            // Ajouter l'année de début si elle n'est pas déjà présente dans le tableau
            if (!in_array($anneeDebut, $annees)) {
                $annees[] = $anneeDebut;
            }
            // Ajouter l'année de fin si elle n'est pas déjà présente dans le tableau
            if (!in_array($anneeFin, $annees)) {
                $annees[] = $anneeFin;
            }
        }
        $professeurs = $tuteurIsenRepository->findAllTuteurIsens();


        return $this->render('home/index.html.twig', [
            'stages' => $stages,
            'noms' => $noms,
            'groupes' => $groupes,
            'etats_stages' => $etats_stages,
            'annees' => $annees,
            'professeurs' => $professeurs,
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
        // Rendu d'une vue partielle contenant uniquement les données de la table
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
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

    #[Route('/trier/apprenant', name: 'trier-apprenant')]
    public function trierApprenant(Request $request, StageRepository $stageRepository) {
        // Récupération des paramètres de requête
        $des = $request->query->get('desc');
        // Récupération des stages filtrés en fonction des paramètres
        $stages = $stageRepository->trierstage('apprenant',$des);
        // Rendu d'une vue partielle contenant uniquement les données de la table
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
        ]);
    }
    #[Route('/trier/date', name: 'trier-date')]
    public function trierdate(Request $request, StageRepository $stageRepository) {
        $des = $request->query->get('desc');
        $stages = $stageRepository->trierstage('date',$des);
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
        ]);
    }
    #[Route('/trier/titre', name: 'trier-titre')]
    public function triertitre(Request $request, StageRepository $stageRepository) {
        $des = $request->query->get('desc');
        $stages = $stageRepository->trierstage('date',$des);
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
        ]);
    }
    #[Route('/trier/tuteur', name: 'trier-tuteur')]
    public function triertuteur(Request $request, StageRepository $stageRepository) {
        $des = $request->query->get('desc');
        $stages = $stageRepository->trierstage('tuteur',$des);
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
        ]);
    }
    #[Route('/trier/soutenance', name: 'trier-soutenance')]
    public function triersoutenance(Request $request, StageRepository $stageRepository) {
        $des = $request->query->get('desc');
        $stages = $stageRepository->trierstage('soutenance',$des);
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
        ]);
    }
    #[Route('/trier/rapport', name: 'trier-rapport')]
    public function trierrapport(Request $request, StageRepository $stageRepository) {
        $des = $request->query->get('desc');
        $stages = $stageRepository->trierstage('rapport',$des);
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
        ]);
    }
    #[Route('/trier/eval', name: 'trier-eval')]
    public function triereval(Request $request, StageRepository $stageRepository) {
        $des = $request->query->get('desc');
        $stages = $stageRepository->trierstage('eval',$des);
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
        ]);
    }


}
