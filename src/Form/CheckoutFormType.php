<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
class CheckoutFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?Address $defaultAddress */
        $defaultAddress = $options['default_address'];

        $builder
            ->add('addressLine', TextType::class, [
                'label' => 'Adresse de livraison',
                'empty_data' => '',
                'data' => $defaultAddress?->getAddressLine() ?? '',
                'constraints' => [new NotBlank(message: 'Veuillez saisir votre adresse.')],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'empty_data' => '',
                'data' => $defaultAddress?->getCity() ?? '',
                'constraints' => [new NotBlank(message: 'Veuillez saisir votre ville.')],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'empty_data' => '',
                'data' => $defaultAddress?->getPostalCode() ?? '',
                'constraints' => [new NotBlank(message: 'Veuillez saisir votre code postal.')],
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'preferred_choices' => ['FR'],
                'data' => $defaultAddress?->getCountry() ?? 'FR',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default_address' => null,
        ]);
        $resolver->setAllowedTypes('default_address', ['null', Address::class]);
    }
}
