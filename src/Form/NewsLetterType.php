<?php

namespace App\Form;

use App\Entity\Newsletter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsLetterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('object', TextType::class,[
                'required'=> true,
                'attr'=>[
                    'placeholder'=>'Objet'
                ]
            ])
            ->add('contenu')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Newsletter::class,
        ]);
    }
}
