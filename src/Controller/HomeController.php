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
use PHPUnit\TextUI\XmlConfiguration\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Importer la classe EntityType


class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(Request $request, StageRepository $stageRepository, GroupeRepository $groupRepository, ApprenantRepository $apprenantRepository, TuteurIsenRepository $tuteurIsenRepository): Response
    {
        $stages = $stageRepository->findAllStages();
        $noms = $apprenantRepository->findAll();
        $groupes = $groupRepository->findAll();
        $etats_stages = [['id'=>1 , 'libelle'=>'En cours'], ['id'=>1 , 'libelle'=>'Terminé'] ];
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
        $professeurs = $tuteurIsenRepository->findAll();


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
        // Récupération des stages filtrés en fonction des paramètres
        $stages = $stageRepository->findByApprenantNom($nom2);
        // Rendu d'une vue partielle contenant uniquement les données de la table
        return $this->render('home/_table.html.twig', [
            'stages' => $stages,
        ]);
    }

    #[Route('/statistique', name: 'app_statistique')]
    public function statistique(): Response
    {
        return $this->render('statistique/statistique.html.twig');
    }

    #[Route('/ajouter', name: 'ajouter')]
    public function ajouterStage(Request $request, StageRepository $stageRepository): Response
    {
        $stage = new Stage();
        $form = $this->createForm(AjoutstageType::class, $stage);

        // Modifier le formulaire pour le champ tuteur_isen
        $form->add('tuteur_isen', EntityType::class, [
            'class' => TuteurIsen::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
            }, // Choisir le champ à afficher dans le champ visible
            'placeholder' => 'Choisir un tuteur isen',
            // D'autres options si nécessaire
        ]);
        $form->add('tuteur_stage', EntityType::class, [
            'class' => TuteurStage::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
            }, // Choisir le champ à afficher dans le champ visible
            'placeholder' => 'Choisir un tuteur de Stage',
            // D'autres options si nécessaire
        ]);
        $form->add('apprenant', EntityType::class, [
            'class' => Apprenant::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
            }, // Choisir le champ à afficher dans le champ visible
            'placeholder' => 'Choisir un apprenant',
            // D'autres options si nécessaire
        ]);
        $form->add('entreprise', EntityType::class, [
            'class' => Entreprise::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom();
            },
            'placeholder' => 'Choisir une entreprise',
        ]);
        $form->add('groupe', EntityType::class, [
            'class' => Groupe::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getLibelle();
            },
            'placeholder' => 'Choisir un groupe ',
        ]);
        $form->add('soutenance', EntityType::class, [
            'class' => Etat::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getLibelle();
            },
            'placeholder' => 'Choisir un état pour la soutenance ',
        ]);
        $form->add('rapport', EntityType::class, [
            'class' => Etat::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getLibelle();
            },
            'placeholder' => 'Choisir un état pour le rapport ',
        ]);
        $form->add('eval_entreprise', EntityType::class, [
            'class' => Etat::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getLibelle();
            },
            'placeholder' => 'Choisir un état pour l\'evaluation d\'enteprise ',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stageRepository->addStage($stage);
            return $this->redirectToRoute('app_home');
        }

        return $this->render('form/ajouter_stage.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/ajouter-un-tuteur-isen', name: 'ajouter_un_tuteur_isen')]
    public function ajouterTuteurIsen(Request $request, TuteurIsenRepository $TuteurRepository): Response
    {
        $tuteur = new TuteurIsen();
        $form = $this->createForm(TuteurIsenType::class, $tuteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $TuteurRepository->addTuteurIsen($tuteur);
            return $this->redirectToRoute('app_home');
        }

        return $this->render('form/ajouter_un_tuteur_isen.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
