<?php

namespace App\Form;

use App\Entity\Roles;
use App\Form\DroitsUtilisateurType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, ['required' => true, 'label' => 'libellé', 'disabled' => !$options["libelle"]])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('droitsUtilisateur', DroitsUtilisateurType::class)
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Roles::class,
            'libelle' => true
        ]);
    }
}
