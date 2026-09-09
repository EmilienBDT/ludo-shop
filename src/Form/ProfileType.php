<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre prénom.'),
                    new Length(max: 100, maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères.'),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre nom.'),
                    new Length(max: 100, maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.'),
                ],
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'choice',
                'years' => range(date('Y'), date('Y') - 100),
                'placeholder' => ['year' => 'Année', 'month' => 'Mois', 'day' => 'Jour'],
                'required' => false,
                'constraints' => [],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
