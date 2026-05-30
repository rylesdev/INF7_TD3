<?php

namespace App\Form;

use App\Entity\AvisAnnonce;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvisAnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', ChoiceType::class, [
                'label'    => 'Note',
                'choices'  => ['⭐' => 1, '⭐⭐' => 2, '⭐⭐⭐' => 3, '⭐⭐⭐⭐' => 4, '⭐⭐⭐⭐⭐' => 5],
                'expanded' => true,
                'attr'     => ['class' => 'd-flex gap-3'],
            ])
            ->add('commentaire', TextareaType::class, [
                'label'    => 'Votre avis',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Partagez votre expérience…'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => AvisAnnonce::class]);
    }
}
