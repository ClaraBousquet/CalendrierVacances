<?php

namespace App\Form;

use App\Entity\Type;
use App\Entity\User;
use App\Entity\Conges;
use App\Entity\Groupe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CongesStatutsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    { $builder
            ->add('statut', ChoiceType::class, [
                'choices'  => [
                    'Refusé' => 'refusé',
                    'Accepté' => 'accepté',
                    'Annulé' => 'annulé',
                ],
                'expanded' => false,
                'multiple' => false,
                'label' => 'Statut',
                'attr' => [
                    'class' => 'fc-button fc-button-primary'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conges::class,
        ]);
    }
}
