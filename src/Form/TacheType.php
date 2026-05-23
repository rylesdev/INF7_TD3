<?php

namespace App\Form;

use App\Entity\Colocation;
use App\Entity\Tache;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $colocation = $options['colocation'];
        $membres    = [];
        if ($colocation) {
            $membres[] = $colocation->getProprietaire();
            foreach ($colocation->getChambres() as $chambre) {
                if ($chambre->getLocataire()) {
                    $membres[] = $chambre->getLocataire();
                }
            }
        }

        $builder
            ->add('titre', TextType::class, ['label' => 'Titre', 'attr' => ['class' => 'form-control']])
            ->add('type', ChoiceType::class, [
                'label'   => 'Type',
                'choices' => [
                    'Vaisselle'  => Tache::TYPE_VAISSELLE,
                    'Ménage'     => Tache::TYPE_MENAGE,
                    'Entretien'  => Tache::TYPE_ENTRETIEN,
                    'Autre'      => Tache::TYPE_AUTRE,
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('dateEcheance', DateType::class, [
                'label'    => 'Jour prévu',
                'widget'   => 'single_text',
                'required' => false,
                'attr'     => ['class' => 'form-control'],
            ])
            ->add('assigne', EntityType::class, [
                'class'        => User::class,
                'choices'      => $membres,
                'choice_label' => 'nomComplet',
                'label'        => 'Assigné à',
                'required'     => false,
                'placeholder'  => 'Non assigné',
                'attr'         => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Tache::class, 'colocation' => null]);
    }
}
