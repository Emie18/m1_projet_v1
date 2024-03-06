<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\StageRepository;
use App\Repository\TuteurIsenRepository;
use App\Repository\GroupeRepository;
use App\Form\AjoutstageType;
use App\Form\TuteurIsenType;
use App\Form\FormCSVType;
use App\Form\TuteurType;
use App\Form\ApprenantType;
use App\Form\EntrepriseType;

use App\Entity\Stage;
use App\Entity\TuteurIsen;
use App\Entity\TuteurStage;
use App\Entity\Apprenant;
use App\Entity\Entreprise;
use App\Entity\Etat;
use App\Entity\Groupe;
use App\Repository\ApprenantRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\EtatRepository;
use App\Repository\TuteurStageRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Query\ResultSetMapping;
use League\Csv\Reader;

use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Importer la classe EntityType
use Symfony\Bundle\MakerBundle\Str;

class BackController extends AbstractController
{
    private $entityManager;
    private $buffApprenant;
    private $buffTuteurIsen;
    private $buffTuteurStage;
    private $buffEntreprise;
    private $buffGroupe;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
        $this->buffApprenant = [];
        $this->buffTuteurIsen = [];
        $this->buffTuteurStage = [];
        $this->buffEntreprise = [];
        $this->buffGroupe = [];
    }
    
    #[Route('/back/', name: 'app_back')]
    public function index(Request $request, StageRepository $stageRepository, GroupeRepository $groupRepository, ApprenantRepository $apprenantRepository, TuteurIsenRepository $tuteurIsenRepository): Response
    {
        $stages = $stageRepository->findAllStages();
        $noms = $apprenantRepository->findAllApprenants();
        $groupes = $groupRepository->findAll();
        $etats_stages = [['id'=>1 , 'libelle'=>'Terminé'], ['id'=>2 , 'libelle'=>'En cours'] ];
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


        return $this->render('back/index.html.twig', [
            'stages' => $stages,
            'noms' => $noms,
            'groupes' => $groupes,
            'etats_stages' => $etats_stages,
            'annees' => $annees,
            'professeurs' => $professeurs,
        ]);
    
    }
    #[Route('/back/ajouter-stage', name: 'ajouter_stage')]
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
                return $tuteur->getNom();},
            'placeholder' => 'Choisir une entreprise',
        ]);
        $form->add('groupe', EntityType::class, [
            'class' => Groupe::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getLibelle();},
            'placeholder' => 'Choisir un groupe ',
        ]);
        $form->add('soutenance', EntityType::class, [
            'class' => Etat::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getLibelle();},
            'placeholder' => 'Choisir un état pour la soutenance ',
        ]);
        $form->add('rapport', EntityType::class, [
            'class' => Etat::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getLibelle();},
            'placeholder' => 'Choisir un état pour le rapport ',
        ]);
        $form->add('eval_entreprise', EntityType::class, [
            'class' => Etat::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getLibelle();},
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

    #[Route('/back/ajouter-tuteur-isen', name: 'ajouter_tuteur_isen')]
    public function ajouterTuteurIsen(Request $request, TuteurIsenRepository $TuteurRepository): Response
    {
        $tuteur = new TuteurIsen();
        $form = $this->createForm(TuteurIsenType::class, $tuteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $TuteurRepository->addTuteurIsen($tuteur);
            return $this->redirectToRoute('app_home');
        }

        return $this->render('form/ajouter_personne.html.twig', [
            'form' => $form->createView(),
            'title' => "un tuteur ISEN",
        ]);
    }
    
    #[Route('/back/import-file', name: "importe_file")]
    public function importFile(Request $request): Response{
        $form = $this->createForm(FormCSVType::class);
        $form->handleRequest($request);
        //$entityManager = $this->getDoctrine()->getManager();
        if($form->isSubmitted() && $form->isValid()){
            $csvFile = $form->get('csvFile')->getData();
            $csvFilePath = $csvFile->getRealPath();
            $this->checkEtat();
            if(($handle = fopen($csvFilePath, "r")) != FALSE){
                $reader = Reader::createFromPath($csvFilePath, 'r');
                $reader->setDelimiter(';');
                $firstLine = 0;
                $etat = $this->entityManager->getRepository(Etat::class)->findOneBy([
                    "libelle" => "???"
                ]);
                $nombreStagesAjoutes = 0;
                foreach ($reader as $row) {
                    //sauter la première ligne
                    if($firstLine == 0){
                        $firstLine++;
                        continue;
                    }
                    if($row[0] == NULL) break;
                    //vérifier si les éléments existent déjà
                    $this->checkApprenant($row[0], $row[1], $row[2]);
                    $this->checkTuteurStage($row[8], $row[9], $row[10]);
                    $this->checkTuteurIsen($row[12], $row[13], $row[14]);
                    $this->checkGroupe($row[3]);
                    $this->checkEntreprise($row[11]);
                    if ($this->addStage($row, $etat)) {
                        // Incrémenter le compteur des stages ajoutés
                        $nombreStagesAjoutes++;
                    }
                }
                return $this->render('form/message.html.twig', [
                    'nb_stage' => $nombreStagesAjoutes
                ]);


            }
            //return $this->redirectToRoute("app_back");
        }
        return $this->render('form/csv_import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/back/ajouter-tuteur-stage', name: 'ajouter_tuteur_stage')]
    public function ajouterTuteur(Request $request, TuteurStageRepository $TuteurRepository){
        $tuteur = new TuteurStage();
        $form = $this->createForm(TuteurType::class, $tuteur);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $TuteurRepository->addTuteurStage($tuteur);
            return $this->redirectToRoute('app_home');
        }
        return $this->render('form/ajouter_personne.html.twig', [
            'form' => $form->createView(),
            'title' => "un tuteur de stage",
        ]);

    }
    #[Route('/back/ajouter-apprenant', name: 'ajouter_apprenant')]
    public function ajouterApprenant(Request $request, ApprenantRepository $ApprenantRepository){
        $apprenant = new Apprenant();
        $form = $this->createForm(ApprenantType::class, $apprenant);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $ApprenantRepository->addApprenant($apprenant);
            return $this->redirectToRoute('app_home');
        }
        return $this->render('form/ajouter_personne.html.twig', [
            'form' => $form->createView(),
            'title' => "un apprenant",
        ]);
    }
    #[Route('/back/ajouter-entreprise', name: 'ajouter_entreprise')]
    public function ajouterEntreprise(Request $request, EntrepriseRepository $EntrepriseRepository){
        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseType::class, $entreprise);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $EntrepriseRepository->addEntreprise($entreprise);
            return $this->redirectToRoute('app_home');
        }
        return $this->render('form/ajouter_personne.html.twig', [
            'form' => $form->createView(),
            'title' => "une entreprise",
        ]);
    }
    #[Route ('/back/tuteur-isen', name : 'tuteur_isen')]
    public function showTuteurIsen(Request $request, TuteurIsenRepository $TuteurRepository){
        $tuteur = $TuteurRepository->findAll();
        return $this->render('back/index.html.twig', [
            'personnes' =>$tuteur,
        ]);
    }
    #[Route ('/back/tuteur-stage', name : 'tuteur_stage')]
    public function showTuteurStage(Request $request, TuteurStageRepository $TuteurRepository){
        $tuteur = $TuteurRepository->findAll();
        return $this->render('back/index.html.twig', [
            'personnes' =>$tuteur,
        ]);
    }
    #[Route ('/back/apprenant', name: 'apprenant')]
    public function showApprenant(Request $request, ApprenantRepository $ApprenantRepository){
        $apprenant = $ApprenantRepository->findAll();
        return $this->render('back/index.html.twig', [
            'personnes' =>$apprenant,
        ]);
    }
    #[Route ('/back/entreprise', name: 'entreprise')]
    public function showEntreprise(Request $request, EntrepriseRepository $EntrepriseRepository){
        $entreprise = $EntrepriseRepository->findAll();
        return $this->render('back/index.html.twig', [
            'entreprises' =>$entreprise,
        ]);
    }
    #[Route('/back/statistique', name : 'statistiques')]
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


        return $this->render('back/statistique.html.twig', [
            'statistics' => $statistics,
            'pourcentage' => $statisticsWithPercentage,
            'statMois' => $statisticsDay,

        ]);
    }
    /**
     * vérifier si un apprenant est déjà dans la base
     * @param int $num numéro de l'apprenant
     * @param string $nom le nom de famille de l'apprenant
     * @param string $prenom le prénom de l'apprenant
     */
    public function checkApprenant($num, $nom, $prenom){
        $app = $this->entityManager->getRepository(Apprenant::class)->findOneBy([
            "num_apprenant" => intval($num)
        ]);
        
        if($app == NULL){
            $newEntity = new Apprenant();
            $newEntity->setNumApprenant(intval($num));
            $newEntity->setNom($nom);
            $newEntity->setPrenom($prenom);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();
            $this->buffApprenant[$num] = $newEntity;
        }else $this->buffApprenant[$num] = $app;
        
    }
    /**
     * vérifier si un tuteur de stage est déjà dans la base
     * @param int $num numéro du tuteur de stage
     * @param string $nom le nom de famille du tuteur de stage
     * @param string $prenom le prénom du tuteur de stage
     */
    public function checkTuteurStage($num, $nom, $prenom){
        $app = $this->entityManager->getRepository(TuteurStage::class)->findOneBy([
            "num_tuteur_stage" => $num
        ]);
        if($app == NULL){
            $newEntity = new TuteurStage();
            $newEntity->setNumTuteurStage(intval($num));
            $newEntity->setNom($nom);
            $newEntity->setPrenom($prenom);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();
            $this->buffTuteurStage[$num] = $newEntity;
        }   
         
    }
    /**
     * vérifier si un tuteur de l'ISEN est déjà dans la base
     * @param int $num numéro du tuteur de l'ISEN
     * @param string $nom le nom de famille du tuteur de l'ISEN
     * @param string $prenom le prénom du tuteur de l'ISEN
     */
    public function checkTuteurIsen($num, $nom, $prenom){
        $app = $this->entityManager->getRepository(TuteurIsen::class)->findOneBy([
            "num_tuteur_isen" => $num
        ]);
        if($app == NULL){
            $newEntity = new TuteurIsen();
            $newEntity->setNumTuteurIsen(intval($num));
            $newEntity->setNom($nom);
            $newEntity->setPrenom($prenom);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();
            $this->buffTuteurIsen[$num] = $newEntity;
        }
    }
    /**
     * vérifier si un groupe est déjà dans la base
     * @param string $nom le nom du groupe
     */
    public function checkGroupe($nom){
        $app = $this->entityManager->getRepository(Groupe::class)->findOneBy([
            "libelle" => $nom
        ]);
        if($app == NULL){
            $newEntity = new Groupe();
            $newEntity->setLibelle($nom);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();
            $this->buffGroupe[$nom] = $newEntity;
        }
    }
    /**
     * vérifier si une entreprise est déjà dans la base
     * @param string $nom le nom de l'entreprise
     */
    public function checkEntreprise($nom){
        $app = $this->entityManager->getRepository(Entreprise::class)->findOneBy([
            "nom" => $nom
        ]);
        if($app == NULL){
            $newEntity = new Entreprise();
            $newEntity->setNom($nom);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();
            $this->buffEntreprise[$nom] = $newEntity;
        }
    }
    public function checkEtat(){
        $libelle = $this->entityManager->getRepository(Etat::class)->findAll();

        if(count($libelle) != 4){
            if(!in_array("Valide", $libelle)){
                $newEntity = new Etat();
                $newEntity->setLibelle("Valide");
                $this->entityManager->persist($newEntity);
                $this->entityManager->flush();
            }            
            if(!in_array("Non-valide", $libelle)){
                $newEntity = new Etat();
                $newEntity->setLibelle("Non-valide");
                $this->entityManager->persist($newEntity);
                $this->entityManager->flush();
            }
            if(!in_array("Valide apres non-valide", $libelle)){
                $newEntity = new Etat();
                $newEntity->setLibelle("Valide apres non-valide");
                $this->entityManager->persist($newEntity);
                $this->entityManager->flush();            }
            if(!in_array("???", $libelle)){
                $newEntity = new Etat();
                $newEntity->setLibelle("???");
                $this->entityManager->persist($newEntity);
                $this->entityManager->flush();            }
        }
    }
    /**
     * ajouter le stage
     * @param Array $row l'ensemble des données du addStage
     * @param Etat $etat état non défini
     */
    public function addStage($row, $etat): bool
{
    $dateDebut = $row[4];
    $dateFin = $row[5];
    $dateSoutenance = $row[18] . " " . $row[19];
    
    // Exception si le format de la date diffère
    if(strlen($dateDebut) == 10){
        $dateDebut = $dateDebut . " 08:00";
    }        
    if(strlen($dateFin) == 10){
        $dateFin = $dateFin . " 08:00";
    }
    if(strlen($dateDebut) == 0){
        $dateDebut = "01/01/0001 08:00";
    }        
    if(strlen($dateFin) == 0){
        $dateFin = "01/01/0001 08:00";
    }

    $app = $this->entityManager->getRepository(Stage::class)->findOneBy([
        "num_stage" => intval($row[16])
    ]);

    if($app == NULL){
        $newEntity = new Stage();
        $newEntity->setTitre($row[17]);
        $dateDebut = \DateTime::createFromFormat("d/m/Y H:i", $dateDebut);
        $dateFin = \DateTime::createFromFormat("j/n/Y H:i", $dateFin);
        $dateSoutenance = \DateTime::createFromFormat("j/n/Y H:i", $dateSoutenance);
        $newEntity->setDateDebut($dateDebut);
        $newEntity->setDateFin($dateFin);
        $newEntity->setDescription($row[20]);
        $newEntity->setNumStage(intval($row[16]));
        
        // Foreign key
        if(array_key_exists($row[0], $this->buffApprenant)){
            $newEntity->setApprenant($this->buffApprenant[$row[0]]);
        }else{
            $newEntity->setApprenant($this->entityManager->getRepository(Apprenant::class)->findOneBy([
                "num_apprenant" => intval($row[0])
            ]));
        }
        if(array_key_exists($row[12], $this->buffTuteurIsen)) {
            $newEntity->setTuteurIsen($this->buffTuteurIsen[$row[12]]);
        } else {
            $newEntity->setTuteurIsen($this->entityManager->getRepository(TuteurIsen::class)->findOneBy([
                "num_tuteur_isen" => intval($row[12])
            ]));
        }
        if(array_key_exists($row[8], $this->buffTuteurStage)){
            $newEntity->setTuteurStage($this->buffTuteurStage[$row[8]]);
        } else {
            $newEntity->setTuteurStage($this->entityManager->getRepository(TuteurStage::class)->findOneBy([
                "num_tuteur_stage" => intval($row[8])
            ]));
        }
        if(array_key_exists($row[11], $this->buffEntreprise)){
            $newEntity->setEntreprise($this->buffEntreprise[$row[11]]);
        } else {
            $newEntity->setEntreprise($this->entityManager->getRepository(Entreprise::class)->findOneBy([
                "nom" => $row[11]
            ]));
        }
        if(array_key_exists($row[3], $this->buffGroupe)){
            $newEntity->setGroupe($this->buffGroupe[$row[3]]);
        } else {
            $newEntity->setGroupe($this->entityManager->getRepository(Groupe::class)->findOneBy([
                "libelle" => $row[3]
            ]));
        }
        $newEntity->setSoutenance($etat);
        if($dateSoutenance) {
            $newEntity->setDateSoutenance($dateSoutenance);
        }
        $newEntity->setRapport($etat);
        $newEntity->setEvalEntreprise($etat);
        $this->entityManager->persist($newEntity);
        $this->entityManager->flush();

        return true; // Le stage a été ajouté avec succès
    }

    return false; // Aucun stage ajouté
}

}