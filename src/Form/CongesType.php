<?php

namespace App\Form;

use App\Entity\Conges;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class CongesType extends AbstractType
{
    private Security $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();
        $isCadre = $user->isCadre();
        $userId = $user->getId();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
            ]);

        $builder->add('type', EntityType::class, [
            'class' => Type::class,
            'choice_label' => 'label',
            'label' => 'Type de congé',
            'query_builder' => function ($repository) use ($isCadre, $userId) {
                $qb = $repository->createQueryBuilder('t');
                if (!$isCadre) {
                    $qb
                        ->where('t.requiresIsCadre = :requiresIsCadre')
                        ->setParameter('requiresIsCadre', false)
                    ;
                }
                $qb
                    ->andWhere('t.is_restricted_user = :is_restricted_user')
                    ->setParameter('is_restricted_user', false)
                    ->orWhere('t.is_restricted_user = true AND :userId MEMBER OF t.restricted_user')
                    ->setParameter('userId', $userId)
                ;

                return $qb;
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conges::class,
        ]);
    }
}
