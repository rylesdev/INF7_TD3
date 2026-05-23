<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'Votre email', 'attr' => ['class' => 'form-control']])
            ->add('password', RepeatedType::class, [
                'type'           => PasswordType::class,
                'mapped'         => false,
                'first_options'  => ['label' => 'Nouveau mot de passe', 'attr' => ['class' => 'form-control']],
                'second_options' => ['label' => 'Confirmer', 'attr' => ['class' => 'form-control']],
                'constraints'    => [
                    new NotBlank(),
                    new Length(['min' => 8]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
