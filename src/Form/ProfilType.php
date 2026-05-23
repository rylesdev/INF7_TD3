<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, ['label' => 'Prénom', 'attr' => ['class' => 'form-control']])
            ->add('nom', TextType::class, ['label' => 'Nom', 'attr' => ['class' => 'form-control']])
            ->add('email', EmailType::class, ['label' => 'Email', 'attr' => ['class' => 'form-control']])
            ->add('telephone', TextType::class, ['label' => 'Téléphone', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('photoFile', FileType::class, [
                'label'       => 'Photo de profil',
                'mapped'      => false,
                'required'    => false,
                'attr'        => ['accept' => 'image/jpeg,image/png,image/webp', 'class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize'          => '2M',
                        'mimeTypes'        => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Format non autorisé.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
