<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
class AddressFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Libellé',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir un libellé.'),
                ],
            ])
            ->add('addressLine', TextType::class, [
                'label' => 'Adresse',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre adresse.'),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre code postal.'),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre ville.'),
                ],
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'preferred_choices' => ['FR'],
                'data' => 'FR',
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => 'Définir comme adresse par défaut',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
