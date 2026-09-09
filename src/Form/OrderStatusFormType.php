<?php

namespace App\Form;

use App\Enum\OrderStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class OrderStatusFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Nouveau statut',
                'choices' => $options['statuses'],
                'choice_label' => fn (OrderStatus $status): string => $status->label(),
                'choice_value' => fn (?OrderStatus $status): ?string => $status?->value,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Mettre à jour']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'statuses' => [],
        ]);
    }
}
