<?php

// src/Form/ModifierEtatType.php

namespace App\Form;
use App\Entity\Apprenant;
use App\Entity\Entreprise;
use App\Entity\Etat;
use App\Entity\Groupe;
use App\Entity\Stage;
use App\Entity\TuteurIsen;
use App\Entity\TuteurStage;
use Symfony\Component\Form\AbstractType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModifierEtatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('soutenance', EntityType::class, [
                    'class' => Etat::class,
        'choice_label' => 'libelle',
                ])
                ->add('eval_entreprise', EntityType::class, [
                    'class' => Etat::class,
        'choice_label' => 'libelle',
                ])
                ->add('rapport', EntityType::class, [
                    'class' => Etat::class,
        'choice_label' => 'libelle',
                ]);
            }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Mettez ici votre entité Stage si vous en avez une
            'data_class' => Stage::class,
        ]);
    }
}
