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
use App\Form\ModifierEtatType;

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
use DateTime;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Query\ResultSetMapping;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Importer la classe EntityType
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

function convertToLabel($value) {
    if ($value === null) {
        return "Non déterminé";
    } elseif ($value === true) {
        return "Oui";
    } elseif ($value === false) {
        return "Non";
    }
}
class BackController extends AbstractController
{
    private $entityManager;

    private $buffStage;
    private $buffApprenant;
    private $buffTuteurIsen;
    private $buffTuteurStage;
    private $buffEntreprise;
    private $buffGroupe;

    private $idApprenant;
    private $nomApprenant;
    private $prenomApprenant;
    private $idTuteurStage;
    private $nomTuteurStage;
    private $prenomTuteurStage;
    private $idTuteurIsen;
    private $nomTuteurIsen;
    private $prenomTuteurIsen;
    private $libelleGroupe;
    private $nomEntreprise;
    private $dateDebut;
    private $dateFin;
    private $dateSoutenance;
    private $heureSoutenance;
    private $numStage;
    private $titreStage;
    private $descStage;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
        $this->buffApprenant = [];
        $apprenants = $this->entityManager->getRepository(Apprenant::class)->findAll();
        foreach($apprenants as $a){
            $this->buffApprenant[$a->getNumApprenant()] = $a;
        }
        $tuteurIsen = $this->entityManager->getRepository(TuteurIsen::class)->findAll();
        $this->buffTuteurIsen = [];
        foreach($tuteurIsen as $t){
            $this->buffTuteurIsen[$t->getNumTuteurIsen()] = $t;
            // array_push($this->buffTuteurIsen, [$t->getNumTuteurIsen() => $t->getId()]);
        }
        $this->buffTuteurStage = [];
        $tuteurStage = $this->entityManager->getRepository(TuteurStage::class)->findAll();
        foreach($tuteurStage as $t){
            $this->buffTuteurStage[$t->getNumTuteurStage()] = $t;
            // array_push($this->buffTuteurStage, [$t->getNumTuteurStage() => $t->getId()]);
        }
        $entreprises = $this->entityManager->getRepository(Entreprise::class)->findAll();
        $this->buffEntreprise = [];
        foreach($entreprises as $e){
            $this->buffEntreprise[$e->getNom()] = $e;
            //array_push($this->buffEntreprise, [$e->getNom() => $e->getId()]);
        }
        $this->buffGroupe = [];
        $groupe = $this->entityManager->getRepository(Groupe::class)->findAll();
        foreach($groupe as $g){
            $this->buffGroupe[$g->getLibelle()] = $g;
            //array_push($this->buffGroupe, [$g->getLibelle() => $g->getId()]);
        }
        $this->buffStage = [];
        $stage = $this->entityManager->getRepository(Stage::class)->findAll();
        foreach($stage as $s){
            $this->buffStage[$s->getNumStage()] = $s;
            //array_push($this->buffStage, [$s->getNumStage() => $s->getId()]);
        }
        //modifier les emplacement des element du CSV
        $this->idApprenant = 0;
        $this->nomApprenant = 1;
        $this->prenomApprenant = 2;
        $this->libelleGroupe = 3;
        $this->dateDebut = 4;
        $this->dateFin = 5;
        $this->idTuteurStage = 8;
        $this->nomTuteurStage = 9;
        $this->prenomTuteurStage = 10;
        $this->nomEntreprise = 11;
        $this->idTuteurIsen = 12;
        $this->nomTuteurIsen = 13;
        $this->prenomTuteurIsen = 14;
        $this->numStage = 16;
        $this->titreStage = 17;
        $this->dateSoutenance = 18;
        $this->heureSoutenance = 19;
        $this->descStage = 20;
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
            'title' => 'Stages',
        ]);
    
    }
    #[Route('/back/fichedetail', name: 'app_back_fichedetail')]

    public function fichedetail(Request $request, StageRepository $stageRepository) {
        $id = $request->query->get('id');
        $stage = $stageRepository->findByID($id);

        $dateDebut = $stage[0]->getDateDebut();
        $dateFin = $stage[0]->getDateFin();
        $difference = $dateFin->diff($dateDebut);
        $difference_mois = $difference->m; // Nombre de mois
        return $this->render('back/fiche_detail.html.twig', [
            'stage' => $stage,
            'nbmois'=> $difference_mois,
        ]);
    }
    #[Route('/back/ajouter-stage', name: 'ajouter_stage')]
    public function ajouterStage(Request $request, StageRepository $stageRepository): Response
    {
        $stage = new Stage();
        $form = $this->createForm(AjoutstageType::class, $stage);
        $form->add('date_debut', DateType::class, [
            'widget' => 'single_text',
            // Autres options si nécessaire
        ]);
        $form->add('date_fin', DateType::class, [
            'widget' => 'single_text',
            // Autres options si nécessaire
        ]);
        // Modifier le formulaire pour le champ tuteur_isen
        $form->add('tuteur_isen', EntityType::class, [
            'class' => TuteurIsen::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
            }, // Choisir le champ à afficher dans le champ visible
            'placeholder' => 'Choisir un tuteur isen',
            'query_builder' => function (TuteurIsenRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.nom', 'ASC');
            },
            // D'autres options si nécessaire
        ]);
        $form->add('tuteur_stage', EntityType::class, [
            'class' => TuteurStage::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
            }, // Choisir le champ à afficher dans le champ visible
            'placeholder' => 'Choisir un tuteur de Stage',
            'query_builder' => function (TuteurStageRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.nom', 'ASC');
            },
            // D'autres options si nécessaire
        ]);
        $form->add('apprenant', EntityType::class, [
            'class' => Apprenant::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
            }, // Choisir le champ à afficher dans le champ visible
            'placeholder' => 'Choisir un apprenant',
            'query_builder' => function (ApprenantRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.nom', 'ASC');
            },
        ]);
        $form->add('entreprise', EntityType::class, [
            'class' => Entreprise::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom();},
            'placeholder' => 'Choisir une entreprise',
            'query_builder' => function (EntrepriseRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.nom', 'ASC');
            },
        ]);
        $form->add('groupe', EntityType::class, [
            'class' => Groupe::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getLibelle();},
            'placeholder' => 'Choisir un groupe ',
        ]);
        $form->add('visio', ChoiceType::class, [
            'choices' => [
                'Oui' => true,
                'Non' => false,
            ],
            'expanded' => true,
            'label' => 'Visio',
            'required' => false,
            'placeholder' => false,
        ]);
        $form->add('rapport_remis', ChoiceType::class, [
            'choices' => [
                'Oui' => true,
                'Non' => false,
            ],
            'expanded' => true,
            'label' => 'Rapport remis',
            'required' => false,
            'placeholder' => false,
        ]);
        $form->add('confidentiel', ChoiceType::class, [
            'choices' => [
                'Oui' => true,
                'Non' => false,
            ],
            'expanded' => true,
            'label' => 'Confidentiel',
            'required' => false,
            'placeholder' => false,
        ]);

        $form->add('date_soutenance', DateTimeType::class, [
            'widget' => 'single_text',
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
            return $this->redirectToRoute('app_back');
        }

        return $this->render('form/ajouter_stage.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/back/modifier-stage/{id}', name: 'modifier_stage')]
    public function modifierStage(Request $request, StageRepository $stageRepository, $id): Response
    {
        $stage = $stageRepository->find($id);
        if (!$stage) {
            throw $this->createNotFoundException('Stage non trouvé avec l\'identifiant '.$id);
        }

        $form = $this->createForm(AjoutstageType::class, $stage);
            $form->add('date_debut', DateType::class, [
                'widget' => 'single_text',
                // Autres options si nécessaire
            ]);
            $form->add('date_fin', DateType::class, [
                'widget' => 'single_text',
                // Autres options si nécessaire
            ]);
            // Modifier le formulaire pour le champ tuteur_isen
            $form->add('tuteur_isen', EntityType::class, [
                'class' => TuteurIsen::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
                }, // Choisir le champ à afficher dans le champ visible
                //'placeholder' => 'Choisir un tuteur isen',
                'query_builder' => function (TuteurIsenRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC');
                },
                // D'autres options si nécessaire
            ]);
            $form->add('tuteur_stage', EntityType::class, [
                'class' => TuteurStage::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
                }, // Choisir le champ à afficher dans le champ visible
                //'placeholder' => 'Choisir un tuteur de Stage',
                'query_builder' => function (TuteurStageRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC');
                },
                // D'autres options si nécessaire
            ]);
            $form->add('apprenant', EntityType::class, [
                'class' => Apprenant::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
                }, // Choisir le champ à afficher dans le champ visible
                //'placeholder' => 'Choisir un apprenant',
                'query_builder' => function (ApprenantRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC');
                },
            ]);
            $form->add('entreprise', EntityType::class, [
                'class' => Entreprise::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getNom();},
                //'placeholder' => 'Choisir une entreprise',
                'query_builder' => function (EntrepriseRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC');
                },
            ]);
            $form->add('groupe', EntityType::class, [
                'class' => Groupe::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getLibelle();},
                //'placeholder' => 'Choisir un groupe ',
            ]);
            $form->add('visio', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'label' => 'Visio',
                'required' => false,
                'placeholder' => false,
            ]);
            $form->add('rapport_remis', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'label' => 'Rapport remis',
                'required' => false,
                'placeholder' => false,
            ]);
            $form->add('confidentiel', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'label' => 'Confidentiel',
                'required' => false,
                'placeholder' => false,
            ]);
            $form->add('commentaire', TextareaType::class, [
                //'attr' => ['rows' => 4], // Définit le nombre de lignes initiales
            ]);
            $form->add('description', TextareaType::class, [
                //'attr' => ['rows' => 20],
                //'attr' => ['cols' => 50] // Définit le nombre de lignes initiales
            ]);

            $form->add('date_soutenance', DateTimeType::class, [
                'widget' => 'single_text',
            ]);

            $form->add('soutenance', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getLibelle();},
                //'placeholder' => 'Choisir un état pour la soutenance ',
            ]);
            $form->add('rapport', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getLibelle();},
                //'placeholder' => 'Choisir un état pour le rapport ',
            ]);
            $form->add('eval_entreprise', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getLibelle();},
                
            ]);

            $form->remove('num_stage');
        // Ajouter d'autres modifications de formulaire si nécessaire

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $soutenance = $form->get('soutenance')->getData();
            $rapport = $form->get('rapport')->getData();
            $evalEntreprise = $form->get('eval_entreprise')->getData();
            $commentaire = $form->get('commentaire')->getData();
            if($commentaire === null){$commentaire = "";}
            $confi = convertToLabel($form->get('confidentiel')->getData());
            $visio = convertToLabel($form->get('visio')->getData());
            $rapport_remis = convertToLabel($form->get('rapport_remis')->getData());
            $tuteurIsen = $form->get('tuteur_isen')->getData();
            $titre = $form->get('titre')->getData();
            $date_soutenance = $form->get('date_soutenance')->getData();
            $description = $form->get('description')->getData();
            $groupe = $form->get('groupe')->getData()->getLibelle();
            $apprenant = $form->get('apprenant')->getData()->getNom()." ".$form->get('apprenant')->getData()->getPrenom();
            $entreprise = $form->get('entreprise')->getData();
            $tuteur_stage = $form->get('tuteur_stage')->getData();
            $description = $form->get('description')->getData();
            $dateDebut = $form->get('date_debut')->getData();
            $dateFin = $form->get('date_fin')->getData();
            $difference = $dateFin->diff($dateDebut);
            $difference_mois = $difference->m; // Nombre de mois
            $date_debut_fin =  $dateDebut->format('d/m/Y')." - ".$dateFin->format('d/m/Y'). " ( Durée: ".$difference_mois." mois )";
            $success =$stageRepository->modifierStage($stage);
            if ($success) {
                $operation = "modification réussi";
                return new JsonResponse([
                    'soutenance' => $soutenance->getLibelle(),
                    'rapport' => $rapport->getLibelle(),
                    'evalEntreprise' => $evalEntreprise->getLibelle(),
                    'commentaire' => $commentaire,
                    'confidentel' => $confi,
                    'visio' => $visio,
                    'rapport_remis'=> $rapport_remis,
                    'date_soutenance' => ($date_soutenance ? $date_soutenance->format('d/m/Y à H:i') : "Non déterminée"),
                    'tuteur_isenN'=> $tuteurIsen->getNom(),
                    'tuteur_isenP'=> $tuteurIsen->getPrenom(),
                    'entreprise'=> $entreprise->getNom(),
                    'tuteur_stageN' => $tuteur_stage->getNom(),
                    'tuteur_stageP' => $tuteur_stage->getPrenom(),
                    'date_debut_fin' => $date_debut_fin,
                    'description' => $description,
                    'groupe' => $groupe,
                    'apprenant' => $apprenant,
                    'titre' => $titre,

                ]);

            } else {
                $operation = "modification échouée";
                return new JsonResponse([
                    'operation' => "modification échouée",
                    ]);
            }
        }

        return $this->render('form/modifier_stage.html.twig', [
            'form' => $form->createView(),
            'stage' =>$stage,
        ]);

    }


    #[Route('/back/ajouter-tuteur-isen', name: 'ajouter_tuteur_isen')]
    public function ajouterTuteurIsen(Request $request, TuteurIsenRepository $TuteurRepository): Response
    {
        $tuteur = new TuteurIsen();
        $form = $this->createForm(TuteurIsenType::class, $tuteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tuteur->setNom(strtoupper($tuteur->getNom()));
            $TuteurRepository->addTuteurIsen($tuteur);
            return $this->redirectToRoute('tuteur_isen');
        }

        return $this->render('form/ajouter_personne.html.twig', [
            'form' => $form->createView(),
            'title' => "un tuteur ISEN",
        ]);
    }

    #[Route('/back/modifier-etats/{id}', name: 'modifier_etats')]
    public function modifierEtats(Request $request, $id, StageRepository $stageRepository): Response
    {
        // Récupérer le stage en fonction de l'ID passé en paramètre
        $stage = $stageRepository->findByID($id);
    
        // Vérifier si le stage existe
        if (!$stage) {
            // Gérer le cas où le stage n'est pas trouvé
        }
    
        // Créer le formulaire en utilisant l'objet Stage
        $form = $this->createForm(ModifierEtatType::class, $stage[0]);
    
        // Pré-remplir les champs avec les valeurs récupérées
        $form->get('soutenance')->setData($stage[0]->getSoutenance());
        $form->get('rapport')->setData($stage[0]->getRapport());
        $form->get('eval_entreprise')->setData($stage[0]->getEvalEntreprise());
    
        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        $operation="rien";
        if ($form->isSubmitted() && $form->isValid()) {
           // Récupérer les données soumises du formulaire
        $soutenance = $form->get('soutenance')->getData();
        $rapport = $form->get('rapport')->getData();
        $evalEntreprise = $form->get('eval_entreprise')->getData();

        // Enregistrer les modifications de l'objet Etat
        $success = $stageRepository->updateStage($id, $soutenance, $rapport, $evalEntreprise);

        if ($success) {
            $operation = "modification réussi";
            return new JsonResponse([
                'soutenance' => $soutenance->getLibelle(),
                'rapport' => $rapport->getLibelle(),
                'evalEntreprise' => $evalEntreprise->getLibelle(),
            ]);

        } else {
            $operation = "modification échouée";
        }

        }
        return $this->render('back/modifEtat.html.twig', [
            'stage' =>  $stage,
            'form' => $form->createView(),
            'op'=> $operation,
        ]);
    }
    
    #[Route('/back/import-file', name: "importe_file")]
    public function importFile(Request $request): Response {
        $form = $this->createForm(FormCSVType::class);
        $form->handleRequest($request);
        //$entityManager = $this->getDoctrine()->getManager();
    
        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csvFile')->getData();
            
            // Vérifier l'extension du fichier
            $originalFileName = $csvFile->getClientOriginalName();
            $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
            if ($extension !== 'csv') {
                // Si l'extension n'est pas 'csv', retourner un message d'erreur
                return $this->render('form/message.html.twig', [
                    'error' => "Le fichier doit être au format CSV. Veuillez sélectionner un fichier avec l'extension .csv."
                ]);
            }
    
            $csvFilePath = $csvFile->getRealPath();
            $this->checkEtat();
            

                $reader = Reader::createFromPath($csvFilePath, 'r');
                $reader->setDelimiter(';');
                $firstLine = 0;
                $etat = $this->entityManager->getRepository(Etat::class)->findOneBy(["libelle" => "???"]);
                $nombreStagesAjoutes = 0;
                
    
                foreach ($reader as $row) {
                    //sauter la première ligne
                    if ($firstLine == 0) {
                        $firstLine++;
                        continue;
                    }
                    if($row[0] == NULL) continue;
           
                    if (!isset($row[0]) || !isset($row[1]) || !isset($row[2]) || !isset($row[3]) || !isset($row[8]) || !isset($row[9]) || !isset($row[10]) || !isset($row[11]) || !isset($row[12]) || !isset($row[13]) || !isset($row[14])) {
                        print("coin");
                        return $this->render('form/message.html.twig', [
                            'error' => "Le fichier ne correspond pas à l'import de stage, veuillez vérifier son contenu d'abord."
                        ]);
                    }
                    //vérifier si les éléments existent déjà
                    try{

                        
                        $this->checkApprenant($row[$this->idApprenant], $row[$this->nomApprenant], $row[$this->prenomApprenant]);
                        $this->checkTuteurStage($row[$this->idTuteurStage], $row[$this->nomTuteurStage], $row[$this->prenomTuteurStage]);
                        $this->checkTuteurIsen($row[$this->idTuteurIsen], $row[$this->nomTuteurIsen], $row[$this->prenomTuteurIsen]);
                        $this->checkGroupe($row[$this->libelleGroupe]);
                        $this->checkEntreprise($row[$this->nomEntreprise]);
        
                        if ($this->addStage($row, $etat)) {
                            // Incrémenter le compteur des stages ajoutés
                            $nombreStagesAjoutes++;
                        }
                    }catch (\Exception $e){
                        
                        print($e);
                    }
                }
    
                return $this->render('form/message.html.twig', [
                    'nb_stage' => $nombreStagesAjoutes
                ]);

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
            $tuteur->setNom(strtoupper($tuteur->getNom()));
            $TuteurRepository->addTuteurStage($tuteur);
            return $this->redirectToRoute('tuteur_stage');
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
            $apprenant->setNom(strtoupper($apprenant->getNom()));
            $ApprenantRepository->addApprenant($apprenant);
            return $this->redirectToRoute('apprenant');
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
            return $this->redirectToRoute('entreprise');
        }
        return $this->render('form/ajouter_personne.html.twig', [
            'form' => $form->createView(),
            'title' => "une entreprise",
        ]);
    }
    #[Route ('/back/tuteur-isen', name : 'tuteur_isen')]
    public function showTuteurIsen(Request $request, TuteurIsenRepository $TuteurRepository){
        $tuteur = $TuteurRepository->findAllTuteurIsens();
        return $this->render('back/index.html.twig', [
            'personnes' =>$tuteur,
            'title' => 'tuteurs ISEN'
        ]);
    }
    #[Route ('/back/tuteur-stage', name : 'tuteur_stage')]
    public function showTuteurStage(Request $request, TuteurStageRepository $TuteurRepository){
        $tuteur = $TuteurRepository->findAllTuteurStage();
        return $this->render('back/index.html.twig', [
            'personnes' =>$tuteur,
            'title' => 'tuteurs Stage'
        ]);
    }
    #[Route ('/back/apprenant', name: 'apprenant')]
    public function showApprenant(Request $request, ApprenantRepository $ApprenantRepository){
        $apprenant = $ApprenantRepository->findAllApprenants();
        return $this->render('back/index.html.twig', [
            'personnes' =>$apprenant,
            'title' => 'apprenants'
        ]);
    }
    #[Route ('/back/entreprise', name: 'entreprise')]
    public function showEntreprise(Request $request, EntrepriseRepository $EntrepriseRepository){
        $entreprise = $EntrepriseRepository->findAllEntreprise();
        return $this->render('back/index.html.twig', [
            'entreprises' =>$entreprise,
            'title' => "entreprises"
        ]);
    }
    #[Route('/back/statistique', name : 'statistiques')]
    public function statistique(StageRepository $stageRepository): Response
    {
        $statisticsTuteur = $stageRepository->getStatsTuteurIsen();
        $statisticsEntreprise = $stageRepository->getStatsEntreprise();
        // Création d'un nouveau tableau avec les pourcentages et les noms des entreprises
        $statisticsWithPercentageTuteur = [];
        $statisticsWithPercentageEntreprise = [];

        // Calculer le total des stagiaires
        $totalStagiaires = array_sum(array_column($statisticsTuteur, 'nb_stage'));
        $nbStage = 0;
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
        return $this->render('back/statistique.html.twig', [
            'statistics' => $statisticsTuteur,
            'pourcentage' => $statisticsWithPercentageTuteur,
            'statEntreprise' => $statisticsEntreprise,
            'pourcentageEntreprise' => $statisticsWithPercentageEntreprise,
            'statMois' => $statisticsDay
        ]);
    }
    /**
     * vérifier si un apprenant est déjà dans la base
     * @param int $num numéro de l'apprenant
     * @param string $nom le nom de famille de l'apprenant
     * @param string $prenom le prénom de l'apprenant
     */
    public function checkApprenant($num, $nom, $prenom){
        if(!isset($this->buffApprenant[$num])){
            
            
            $newEntity = new Apprenant();
            $newEntity->setNumApprenant(intval($num));
            $newEntity->setNom($nom);
            $newEntity->setPrenom($prenom);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();
            $this->buffApprenant[$num] = $newEntity;
        }
    }
    /**
     * vérifier si un tuteur de stage est déjà dans la base
     * @param int $num numéro du tuteur de stage
     * @param string $nom le nom de famille du tuteur de stage
     * @param string $prenom le prénom du tuteur de stage
     */
    public function checkTuteurStage($num, $nom, $prenom){
        if(!isset($this->buffTuteurStage[$num])){
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
        if(!isset($this->buffTuteurIsen[$num])){
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
        if(!isset($this->buffGroupe[$nom])){
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
        if(!isset($this->buffEntreprise[$nom])){
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
    $dateDebut = $row[$this->dateDebut];
    $dateFin = $row[$this->dateFin];
    $dateSoutenance = $row[$this->dateSoutenance] . " " . $row[$this->heureSoutenance];
    
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
        "num_stage" => intval($row[$this->numStage])
    ]);

    if(!isset($this->buffStage[$row[$this->numStage]])){
        $newEntity = new Stage();
        $newEntity->setTitre($row[$this->titreStage]);
        $dateDebut = \DateTime::createFromFormat("d/m/Y H:i", $dateDebut);
        $dateFin = \DateTime::createFromFormat("j/n/Y H:i", $dateFin);
        $dateSoutenance = \DateTime::createFromFormat("j/n/Y H:i", $dateSoutenance);
        $newEntity->setDateDebut($dateDebut);
        $newEntity->setDateFin($dateFin);
        $newEntity->setDescription($row[$this->descStage]);
        $newEntity->setNumStage(intval($row[$this->numStage]));
        
        // Foreign key
        // $newEntity->setApprenant($row[$this->idApprenant]);
        // $newEntity->setTuteurIsen($row[$this->idTuteurIsen]);
        // $newEntity->setTuteurStage($row[$this->idTuteurStage]);
        // $newEntity->setEntreprise($row[$this->])
        $newEntity->setApprenant($this->buffApprenant[$row[$this->idApprenant]]);
        $newEntity->setTuteurIsen($this->buffTuteurIsen[$row[$this->idTuteurIsen]]);
        $newEntity->setTuteurStage($this->buffTuteurStage[$row[$this->idTuteurStage]]);
        $newEntity->setEntreprise($this->buffEntreprise[$row[$this->nomEntreprise]]);
        // if(array_key_exists($row[0], $this->buffApprenant)){
        //     $newEntity->setApprenant($this->buffApprenant[$row[0]]);
        // }else{
        //     $newEntity->setApprenant($this->entityManager->getRepository(Apprenant::class)->findOneBy([
        //         "num_apprenant" => intval($row[0])
        //     ]));
        // }
        // if(array_key_exists($row[12], $this->buffTuteurIsen)) {
        //     $newEntity->setTuteurIsen($this->buffTuteurIsen[$row[12]]);
        // } else {
        //     $newEntity->setTuteurIsen($this->entityManager->getRepository(TuteurIsen::class)->findOneBy([
        //         "num_tuteur_isen" => intval($row[12])
        //     ]));
        // }
        // if(array_key_exists($row[8], $this->buffTuteurStage)){
        //     $newEntity->setTuteurStage($this->buffTuteurStage[$row[8]]);
        // } else {
        //     $newEntity->setTuteurStage($this->entityManager->getRepository(TuteurStage::class)->findOneBy([
        //         "num_tuteur_stage" => intval($row[8])
        //     ]));
        // }
        // if(array_key_exists($row[11], $this->buffEntreprise)){
        //     $newEntity->setEntreprise($this->buffEntreprise[$row[11]]);
        // } else {
        //     $newEntity->setEntreprise($this->entityManager->getRepository(Entreprise::class)->findOneBy([
        //         "nom" => $row[11]
        //     ]));
        // }
        // if(array_key_exists($row[3], $this->buffGroupe)){
        //     $newEntity->setGroupe($this->buffGroupe[$row[3]]);
        // } else {
        //     $newEntity->setGroupe($this->entityManager->getRepository(Groupe::class)->findOneBy([
        //         "libelle" => $row[3]
        //     ]));
        // }
        $newEntity->setSoutenance($etat);
        if($dateSoutenance) {
            $newEntity->setDateSoutenance($dateSoutenance);
        }
        $newEntity->setRapport($etat);
        $newEntity->setEvalEntreprise($etat);
        $this->entityManager->persist($newEntity);
        $this->entityManager->flush();
        $this->buffStage[$row[$this->numStage]] = $newEntity;
        return true; // Le stage a été ajouté avec succès
    }

    return false; // Aucun stage ajouté
}

}