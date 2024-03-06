<?php

namespace App\Form;

use App\Entity\Apprenant;
use App\Entity\Entreprise;
use App\Entity\Etat;
use App\Entity\Groupe;
use App\Entity\Stage;
use App\Entity\TuteurIsen;
use App\Entity\TuteurStage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description')
            ->add('commentaire')
            ->add('num_stage')
            ->add('date_soutenance')
            ->add('date_debut')
            ->add('date_fin')
            ->add('tuteur_isen', EntityType::class, [
                'class' => TuteurIsen::class,
'choice_label' => 'id',
            ])
            ->add('tuteur_stage', EntityType::class, [
                'class' => TuteurStage::class,
'choice_label' => 'id',
            ])
            ->add('apprenant', EntityType::class, [
                'class' => Apprenant::class,
'choice_label' => 'id',
            ])
            ->add('entreprise', EntityType::class, [
                'class' => Entreprise::class,
'choice_label' => 'id',
            ])
            ->add('groupe', EntityType::class, [
                'class' => Groupe::class,
'choice_label' => 'id',
            ])
            ->add('soutenance', EntityType::class, [
                'class' => Etat::class,
'choice_label' => 'id',
            ])
            ->add('eval_entreprise', EntityType::class, [
                'class' => Etat::class,
'choice_label' => 'id',
            ])
            ->add('rapport', EntityType::class, [
                'class' => Etat::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stage::class,
        ]);
    }
}
