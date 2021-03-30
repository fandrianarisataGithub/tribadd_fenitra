<?php

namespace App\Form;

use App\Entity\Communaute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommunauteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('roles', CollectionType::class, [
            'entry_type' => RolesType::class,
            'allow_add' => true,
            'by_reference' => false,
            'entry_options' => ['label' => false, 'libelle' => $options["libelle"]],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Communaute::class,
            'libelle' => true
        ]);
    }
}
