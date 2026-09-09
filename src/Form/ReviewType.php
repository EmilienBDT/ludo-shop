<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @extends AbstractType<mixed>
 */
class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => 'Note',
                'choices' => [
                    '1 — Médiocre' => 1,
                    '2 — Passable' => 2,
                    '3 — Correct' => 3,
                    '4 — Bien' => 4,
                    '5 — Excellent' => 5,
                ],
                'expanded' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire (facultatif)',
                'required' => false,
                'constraints' => [
                    new Length(max: 2000, maxMessage: 'Le commentaire ne peut dépasser {{ limit }} caractères.'),
                ],
                'attr' => ['rows' => 4, 'class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
            'csrf_protection' => false,
        ]);
    }
}
