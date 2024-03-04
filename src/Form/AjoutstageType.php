<?php

namespace App\Form;

use App\Entity\Stage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutstageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('num_stage')
            ->add('titre')
            ->add('date_debut')
            ->add('date_fin')
            ->add('description')
            ->add('commentaire')
            ->add('tuteur_isen')
            ->add('tuteur_stage')
            ->add('apprenant')
            ->add('entreprise')
            ->add('groupe')
            ->add('soutenance')
            ->add('eval_entreprise')
            ->add('rapport')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stage::class,
        ]);
    }
}
