<?php

namespace App\Form;

use App\Entity\EvaluationLocataire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationLocataireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', ChoiceType::class, [
                'label'   => 'Note',
                'choices' => [
                    '⭐ Mauvais'    => 1,
                    '⭐⭐ Passable'  => 2,
                    '⭐⭐⭐ Bien'     => 3,
                    '⭐⭐⭐⭐ Très bien' => 4,
                    '⭐⭐⭐⭐⭐ Excellent' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
                'attr'     => ['class' => 'list-unstyled'],
            ])
            ->add('commentaire', TextareaType::class, [
                'label'    => 'Commentaire (facultatif)',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Décrivez votre expérience avec ce locataire…'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => EvaluationLocataire::class]);
    }
}
