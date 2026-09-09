<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\PasswordComplexity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'Adresse e-mail', 'empty_data' => ''])
            ->add('firstName', TextType::class, ['label' => 'Prénom', 'empty_data' => ''])
            ->add('lastName', TextType::class, ['label' => 'Nom', 'empty_data' => ''])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre date de naissance.'),
                    new GreaterThanOrEqual(
                        value: '1900-01-01',
                        message: 'La date de naissance doit être postérieure à 1900.',
                    ),
                    new LessThanOrEqual(
                        value: 'today',
                        message: 'La date de naissance ne peut pas être dans le futur.',
                    ),
                ],
            ])
            ->add('website', TextType::class, [
                'label' => 'Site web',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off',
                    'tabindex' => '-1',
                    'aria-hidden' => 'true',
                    'class' => 'd-none',
                    'style' => 'position:absolute;left:-9999px;',
                ],
                'label_attr' => ['class' => 'd-none'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',
                'first_options' => ['label' => 'Mot de passe', 'attr' => ['autocomplete' => 'new-password']],
                'second_options' => ['label' => 'Confirmation du mot de passe'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir un mot de passe.'),
                    new PasswordComplexity(user: $builder->getData()),
                ],
            ])
            ->add('newsletterSubscribed', CheckboxType::class, [
                'label' => 'Je souhaite recevoir la newsletter',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
