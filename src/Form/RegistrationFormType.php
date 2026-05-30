<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, ['label' => 'Prénom', 'attr' => ['class' => 'form-control', 'autocomplete' => 'given-name']])
            ->add('nom', TextType::class, ['label' => 'Nom', 'attr' => ['class' => 'form-control', 'autocomplete' => 'family-name']])
            ->add('email', EmailType::class, ['label' => 'Email', 'attr' => ['class' => 'form-control', 'autocomplete' => 'email', 'inputmode' => 'email']])
            ->add('telephone', TextType::class, ['label' => 'Téléphone', 'required' => false, 'attr' => ['class' => 'form-control', 'autocomplete' => 'tel', 'type' => 'tel', 'placeholder' => 'ex : +33 6 12 34 56 78']])
            ->add('role', ChoiceType::class, [
                'label'    => 'Je suis',
                'mapped'   => false,
                'choices'  => ['Locataire' => 'locataire', 'Propriétaire' => 'proprietaire'],
                'expanded' => true,
                'attr'     => ['class' => 'form-check'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'mapped'          => false,
                'first_options'   => ['label' => 'Mot de passe', 'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password']],
                'second_options'  => ['label' => 'Confirmer le mot de passe', 'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password']],
                'constraints'     => [
                    new NotBlank(['message' => 'Veuillez saisir un mot de passe.']),
                    new Length(['min' => 8, 'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.']),
                    new Regex([
                        'pattern' => '/^(?=.*[A-Z])(?=.*[0-9])/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule et un chiffre.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
