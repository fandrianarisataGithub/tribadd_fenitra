<?php

namespace App\Form;

use App\Entity\DroitsUtilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DroitsUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('creation', CheckboxType::class, ['label' => 'Création', 'required' => false])
            ->add('modification', CheckboxType::class, ['label' => 'Modification', 'required' => false])
            ->add('visualisation', CheckboxType::class, ['label' => 'Visualisation', 'required' => false])
            ->add('suppression', CheckboxType::class, ['label' => 'Suppression', 'required' => false])
            ->add('commentaire', CheckboxType::class, ['label' => 'Commentaire', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DroitsUtilisateur::class,
        ]);
    }
}
