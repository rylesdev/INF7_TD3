<?php

namespace App\Form;

use App\Entity\Charge;
use App\Entity\Colocation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChargeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label'   => 'Type de charge',
                'choices' => [
                    'Eau'          => Charge::TYPE_EAU,
                    'Électricité'  => Charge::TYPE_ELECTRICITE,
                    'Internet'     => Charge::TYPE_INTERNET,
                    'Taxes'        => Charge::TYPE_TAXES,
                    'Autre'        => Charge::TYPE_AUTRE,
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('montant', MoneyType::class, ['label' => 'Montant (€)', 'currency' => 'EUR', 'attr' => ['class' => 'form-control']])
            ->add('date', DateType::class, ['label' => 'Date', 'widget' => 'single_text', 'attr' => ['class' => 'form-control']])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false, 'attr' => ['class' => 'form-control', 'rows' => 2]])
            ->add('colocation', EntityType::class, [
                'class'        => Colocation::class,
                'choices'      => $options['colocations'],
                'choice_label' => 'nom',
                'label'        => 'Colocation',
                'attr'         => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Charge::class, 'colocations' => []]);
    }
}
