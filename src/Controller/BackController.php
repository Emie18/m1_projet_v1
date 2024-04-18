<?php
/**************************************************************************
* Nom du fichier: BackController.php
* Description: Controller du back
* Auteurs: Emilie Le Rouzic, Thibault Tanné
* Date de création: avril 2024
* Version: 1.0
**************************************************************************/
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

use App\Repository\TuteurStageRepository;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
    private $longDesc;
    private $nbAttribut;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
        $this->buffApprenant = [];
        $apprenants = $this->entityManager->getRepository(Apprenant::class)->findAll();
        $this->buffApprenant[0] = NULL;
        foreach($apprenants as $a){
            
            $this->buffApprenant[$a->getNumApprenant()] = $a;
        }
        $tuteurIsen = $this->entityManager->getRepository(TuteurIsen::class)->findAll();
        $this->buffTuteurIsen = [];
        $this->buffTuteurIsen[0] = NULL;
        foreach($tuteurIsen as $t){
            $this->buffTuteurIsen[$t->getNumTuteurIsen()] = $t;
            
        }
        $this->buffTuteurStage = [];
        $tuteurStage = $this->entityManager->getRepository(TuteurStage::class)->findAll();
        $this->buffTuteurStage[0] = NULL;
        foreach($tuteurStage as $t){
            $this->buffTuteurStage[$t->getNumTuteurStage()] = $t;
            
        }
        $entreprises = $this->entityManager->getRepository(Entreprise::class)->findAll();
        $this->buffEntreprise = [];
        $this->buffEntreprise[0] = NULL;
        foreach($entreprises as $e){
            $this->buffEntreprise[$e->getNom()] = $e;
            
        }
        $this->buffGroupe = [];
        $groupe = $this->entityManager->getRepository(Groupe::class)->findAll();
        $this->buffGroupe[0] = NULL;
        foreach($groupe as $g){
            $this->buffGroupe[$g->getLibelle()] = $g;
            
        }
        $this->buffStage = [];
        $stage = $this->entityManager->getRepository(Stage::class)->findAll();
        $this->buffStage[0] = NULL;
        foreach($stage as $s){
            $this->buffStage[$s->getNumStage()] = $s;
           
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
        $this->longDesc = 1000;
        /*
          nombre d'attribut dans le fichier CSV, utiliser pour vérifier si le fichier est au bon format
          à changer si le fichier change
        */
        $this->nbAttribut = 21;
    }

    #[Route('/back/', name: 'app_back')]
    public function index(StageRepository $stageRepository, GroupeRepository $groupRepository, 
    ApprenantRepository $apprenantRepository, TuteurIsenRepository $tuteurIsenRepository): Response
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
        arsort($annees);

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
        ]);
        $form->add('date_fin', DateType::class, [
            'widget' => 'single_text',
        ]);
        // Modifier le formulaire pour le champ tuteur_isen
        $form->add('tuteur_isen', EntityType::class, [
            'class' => TuteurIsen::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
            }, 
            'placeholder' => 'Choisir un tuteur isen',
            'query_builder' => function (TuteurIsenRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.nom', 'ASC');
            },
        ]);
        $form->add('tuteur_stage', EntityType::class, [
            'class' => TuteurStage::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
            }, 
            'placeholder' => 'Choisir un tuteur de Stage',
            'query_builder' => function (TuteurStageRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.nom', 'ASC');
            },
           
        ]);
        $form->add('apprenant', EntityType::class, [
            'class' => Apprenant::class,
            'choice_label' => function ($tuteur) {
                return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
            }, 
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
            'required' => false,
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
        $form->add('commentaire', TextareaType::class, [
            'attr' => ['rows' => 5], 
            'required' => false,
        ]);
        $form->add('description', TextareaType::class, [
            'attr' => ['rows' => 5],
            
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
            ]);
            $form->add('date_fin', DateType::class, [
                'widget' => 'single_text',
            ]);
            // Modifier le formulaire pour le champ tuteur_isen
            $form->add('tuteur_isen', EntityType::class, [
                'class' => TuteurIsen::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
                }, 
                
                'query_builder' => function (TuteurIsenRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC');
                },
                
            ]);
            $form->add('tuteur_stage', EntityType::class, [
                'class' => TuteurStage::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
                }, 
                'query_builder' => function (TuteurStageRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC');
                }, 
            ]);
            $form->add('apprenant', EntityType::class, [
                'class' => Apprenant::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getNom() . ' ' . $tuteur->getPrenom();
                }, 
                'query_builder' => function (ApprenantRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC');
                },
            ]);
            $form->add('entreprise', EntityType::class, [
                'class' => Entreprise::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getNom();
                },
                'query_builder' => function (EntrepriseRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nom', 'ASC');
                },
            ]);
            $form->add('groupe', EntityType::class, [
                'class' => Groupe::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getLibelle();},
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
            ]);
            $form->add('description', TextareaType::class, [
            ]);

            $form->add('date_soutenance', DateTimeType::class, [
                'widget' => 'single_text',
            ]);

            $form->add('soutenance', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getLibelle();},
            ]);
            $form->add('rapport', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getLibelle();},
            ]);
            $form->add('eval_entreprise', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => function ($tuteur) {
                    return $tuteur->getLibelle();},
                
            ]);

            $form->remove('num_stage');

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
            $date_tt =  $dateDebut->format('d-m-Y')." - ".$dateFin->format('d-m-Y');
            if ($success) {
                $operation = "modification réussi";
                return new JsonResponse([
                    'soutenanceId' => $soutenance->getId(),
                    'soutenance' => $soutenance->getLibelle(),
                    'rapportId' => $rapport->getId(),
                    'rapport' => $rapport->getLibelle(),
                    'evalEntrepriseId' => $evalEntreprise->getId(),
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
                    'date_tt' => $date_tt,
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
            $num = $TuteurRepository->findAll();
            foreach($num as $n){
                if($n->getNumTuteurIsen() == $tuteur->getNumTuteurIsen()){
                    return $this->render('form/ajouter_personne.html.twig', [
                        'form' => $form->createView(),
                        'title' => "un tuteur ISEN",
                        'error' => "Numéro déjà existant",
                    ]); 
                }
            }
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
    
        // Créer le formulaire pour modifier le stage
        $form = $this->createForm(ModifierEtatType::class, $stage[0]);
    
        // Pré-remplir les champs avec les valeurs récupérées
        $form->get('soutenance')->setData($stage[0]->getSoutenance());
        $form->get('rapport')->setData($stage[0]->getRapport());
        $form->get('eval_entreprise')->setData($stage[0]->getEvalEntreprise());
    
        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        $operation="rien";
        if ($form->isSubmitted() && $form->isValid()) {
           // Récupérer les données
            $soutenance = $form->get('soutenance')->getData();
            $rapport = $form->get('rapport')->getData();
            $evalEntreprise = $form->get('eval_entreprise')->getData();

            // Enregistrer les modifications du stage
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
                //vérifier si le fichier contient le bon nombre d'attributs
                for($i = 0; $i < $this->nbAttribut; $i++){
                    if(!isset($row[$i])){
                        return $this->render('form/message.html.twig', [
                            'error' => "Le fichier ne correspond pas à l'import de stage, veuillez vérifier son contenu d'abord."
                        ]);
                    }
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
            $num = $TuteurRepository->findAll();
            foreach($num as $n){
                if($n->getNumTuteurStage() == $tuteur->getNumTuteurStage()){
                    return $this->render('form/ajouter_personne.html.twig', [
                        'form' => $form->createView(),
                        'title' => "un tuteur de stage",
                        'error' => "Numéro déjà existant",
                    ]); 
                }
            }
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
            $num = $ApprenantRepository->findAll();
            foreach($num as $n){
                if($n->getNumApprenant() == $apprenant->getNumApprenant()){
                    return $this->render('form/ajouter_personne.html.twig', [
                        'form' => $form->createView(),
                        'title' => "un apprenant",
                        'error' => "Numéro déjà existant",
                    ]); 
                }
            }
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
    public function showTuteurIsen(TuteurIsenRepository $TuteurRepository){
        $tuteur = $TuteurRepository->findAllTuteurIsens();
        return $this->render('back/index.html.twig', [
            'personnes' =>$tuteur,
            'title' => 'tuteurs ISEN'
        ]);
    }

    #[Route ('/back/tuteur-stage', name : 'tuteur_stage')]
    public function showTuteurStage(TuteurStageRepository $TuteurRepository){
        $tuteur = $TuteurRepository->findAllTuteurStage();
        return $this->render('back/index.html.twig', [
            'personnes' =>$tuteur,
            'title' => 'tuteurs Stage'
        ]);
    }

    #[Route ('/back/apprenant', name: 'apprenant')]
    public function showApprenant(ApprenantRepository $ApprenantRepository){
        $apprenant = $ApprenantRepository->findAllApprenants();
        return $this->render('back/index.html.twig', [
            'personnes' =>$apprenant,
            'title' => 'apprenants'
        ]);
    }

    #[Route ('/back/entreprise', name: 'entreprise')]
    public function showEntreprise(EntrepriseRepository $EntrepriseRepository){
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
        if($num == "") $num = 0;
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
        if($num == "") $num = 0;
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
    /**
     * Verifier si la table état est possède les bon attributs
     */
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
    public function addStage($row, $etat): bool{
        //préparation des dates
        $dateDebut = $row[$this->dateDebut];
        $dateFin = $row[$this->dateFin];
        $dateSoutenance = $row[$this->dateSoutenance] . " " . $row[$this->heureSoutenance];
        
        // Exception si le format de la date diffère
        if(strlen($dateDebut) == 10){
            $dateDebut = $dateDebut . " 08:00";
        }        
        if(strlen($dateFin) == 10){
            $dateFin = $dateFin . " 18:00";
        }
        if(strlen($dateDebut) == 0){
            $dateDebut = "01/01/0001 08:00";
        }        
        if(strlen($dateFin) == 0){
            $dateFin = "01/01/0001 08:00";
        }
        //netoyage des données
        if(strlen($row[$this->titreStage]) == 0) $row[$this->titreStage] = "Non défini";
        if(strlen($row[$this->descStage]) == 0) $row[$this->descStage] = "Non défini";
        if(strlen($row[$this->idTuteurStage]) == 0) $row[$this->idTuteurStage] = 0;
        if(strlen($row[$this->numStage]) == 0) $row[$this->numStage] = 0;
        if(strlen($row[$this->idTuteurIsen]) == 0) $row[$this->idTuteurIsen] = 0;

        if(!isset($this->buffStage[$row[$this->numStage]])){
            $newEntity = new Stage();
            $newEntity->setTitre($row[$this->titreStage]);
            $dateDebut = \DateTime::createFromFormat("d/m/Y H:i", $dateDebut);
            $dateFin = \DateTime::createFromFormat("j/n/Y H:i", $dateFin);
            $dateSoutenance = \DateTime::createFromFormat("j/n/Y H:i", $dateSoutenance);
            $newEntity->setDateDebut($dateDebut);
            $newEntity->setDateFin($dateFin);

            $newEntity->setDescription(substr($row[$this->descStage], 0, $this->longDesc));
            $newEntity->setNumStage(intval($row[$this->numStage]));
            
            // Foreign key
            $newEntity->setApprenant($this->buffApprenant[$row[$this->idApprenant]]);
            $newEntity->setTuteurIsen($this->buffTuteurIsen[$row[$this->idTuteurIsen]]);
            $newEntity->setTuteurStage($this->buffTuteurStage[$row[$this->idTuteurStage]]);
            $newEntity->setEntreprise($this->buffEntreprise[$row[$this->nomEntreprise]]);
            $newEntity->setGroupe($this->buffGroupe[$row[$this->libelleGroupe]]);
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