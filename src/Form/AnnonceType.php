<?php

namespace App\Form;

use App\Entity\Annonce;
use App\Entity\Colocation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, ['label' => 'Titre', 'attr' => ['class' => 'form-control']])
            ->add('description', TextareaType::class, ['label' => 'Description', 'attr' => ['class' => 'form-control', 'rows' => 5]])
            ->add('prix', MoneyType::class, ['label' => 'Loyer mensuel (€)', 'currency' => 'EUR', 'attr' => ['class' => 'form-control']])
            ->add('localisation', TextType::class, ['label' => 'Localisation', 'attr' => ['class' => 'form-control']])
            ->add('statut', ChoiceType::class, [
                'label'   => 'Statut',
                'choices' => ['Disponible' => Annonce::STATUT_DISPONIBLE, 'Indisponible' => Annonce::STATUT_INDISPONIBLE],
                'attr'    => ['class' => 'form-select'],
            ])
            ->add('metaDescription', TextType::class, [
                'label'    => 'Meta description (SEO)',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'maxlength' => 160],
            ])
            ->add('colocation', EntityType::class, [
                'class'        => Colocation::class,
                'choice_label' => 'nom',
                'label'        => 'Colocation',
                'attr'         => ['class' => 'form-select'],
            ])
            ->add('photoFiles', FileType::class, [
                'label'      => 'Photos (JPEG, PNG, WebP)',
                'mapped'     => false,
                'required'   => false,
                'multiple'   => true,
                'attr'       => ['accept' => 'image/jpeg,image/png,image/webp', 'class' => 'form-control'],
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize'          => '5M',
                                'mimeTypes'        => ['image/jpeg', 'image/png', 'image/webp'],
                                'mimeTypesMessage' => 'Format non autorisé (JPEG, PNG, WebP uniquement).',
                            ]),
                        ],
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Annonce::class]);
    }
}
