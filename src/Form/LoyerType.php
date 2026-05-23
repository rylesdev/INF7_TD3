<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Colocation;
use App\Entity\Loyer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoyerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant', MoneyType::class, ['label' => 'Montant (€)', 'currency' => 'EUR', 'attr' => ['class' => 'form-control']])
            ->add('mois', ChoiceType::class, [
                'label'   => 'Mois',
                'choices' => [
                    'Janvier' => '01', 'Février' => '02', 'Mars' => '03', 'Avril' => '04',
                    'Mai' => '05', 'Juin' => '06', 'Juillet' => '07', 'Août' => '08',
                    'Septembre' => '09', 'Octobre' => '10', 'Novembre' => '11', 'Décembre' => '12',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('annee', ChoiceType::class, [
                'label'   => 'Année',
                'choices' => array_combine(range(date('Y'), date('Y') + 2), range(date('Y'), date('Y') + 2)),
                'attr'    => ['class' => 'form-select'],
            ])
            ->add('dateEcheance', DateType::class, [
                'label'  => 'Date d\'échéance',
                'widget' => 'single_text',
                'attr'   => ['class' => 'form-control'],
            ])
            ->add('statut', ChoiceType::class, [
                'label'   => 'Statut',
                'choices' => ['Impayé' => Loyer::STATUT_IMPAYE, 'Payé' => Loyer::STATUT_PAYE, 'En retard' => Loyer::STATUT_EN_RETARD],
                'attr'    => ['class' => 'form-select'],
            ])
            ->add('colocation', EntityType::class, [
                'class'        => Colocation::class,
                'choices'      => $options['colocations'],
                'choice_label' => 'nom',
                'label'        => 'Colocation',
                'attr'         => ['class' => 'form-select'],
            ])
            ->add('chambre', EntityType::class, [
                'class'        => Chambre::class,
                'choice_label' => 'nom',
                'label'        => 'Chambre',
                'required'     => false,
                'placeholder'  => 'Toutes les chambres',
                'attr'         => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Loyer::class, 'colocations' => []]);
    }
}
