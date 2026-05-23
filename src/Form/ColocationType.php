<?php

namespace App\Form;

use App\Entity\Colocation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom de la colocation', 'attr' => ['class' => 'form-control']])
            ->add('adresse', TextType::class, ['label' => 'Adresse', 'attr' => ['class' => 'form-control']])
            ->add('ville', TextType::class, ['label' => 'Ville', 'attr' => ['class' => 'form-control']])
            ->add('codePostal', TextType::class, ['label' => 'Code postal', 'attr' => ['class' => 'form-control', 'maxlength' => 10]])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false, 'attr' => ['class' => 'form-control', 'rows' => 3]])
            ->add('loyer', NumberType::class, ['label' => 'Loyer global (€)', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('latitude', NumberType::class, ['label' => 'Latitude (optionnel)', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('longitude', NumberType::class, ['label' => 'Longitude (optionnel)', 'required' => false, 'attr' => ['class' => 'form-control']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Colocation::class]);
    }
}
