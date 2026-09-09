<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du jeu',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir un nom.'),
                ],
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir une référence.'),
                ],
            ])
            ->add('publisher', TextType::class, ['label' => 'Éditeur', 'required' => false])
            ->add('price', MoneyType::class, [
                'label' => 'Prix HT',
                'currency' => 'EUR',
                'scale' => 2,
                'empty_data' => '0.00',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir un prix.'),
                ],
            ])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'empty_data' => 0,
            ])
            ->add('image', TextType::class, ['label' => 'Chemin de l\'image', 'required' => false])
            ->add('playtimeMinutes', IntegerType::class, ['label' => 'Temps d\'une partie (minutes)', 'required' => false])
            ->add('setupMinutes', IntegerType::class, ['label' => 'Temps de mise en place (minutes)', 'required' => false])
            ->add('minAge', IntegerType::class, ['label' => 'Âge minimum (ans)', 'required' => false])
            ->add('maxAge', IntegerType::class, ['label' => 'Âge maximum (ans)', 'required' => false])
            ->add('minPlayers', IntegerType::class, ['label' => 'Joueurs min', 'required' => false])
            ->add('maxPlayers', IntegerType::class, ['label' => 'Joueurs max', 'required' => false])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Produit actif (visible dans le catalogue)',
                'required' => false,
            ])
            ->add('isMature', CheckboxType::class, [
                'label' => 'Contenu mature (+18)',
                'required' => false,
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'label' => 'Catégories',
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('promoPrice', MoneyType::class, [
                'label' => 'Prix promotionnel HT',
                'currency' => 'EUR',
                'scale' => 2,
                'required' => false,
                'empty_data' => null,
            ])
            ->add('promoStartsAt', DateTimeType::class, [
                'label' => 'Début de la promotion',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('promoEndsAt', DateTimeType::class, [
                'label' => 'Fin de la promotion',
                'widget' => 'single_text',
                'required' => false,
            ])
        ;

        $builder->addEventListener(
            \Symfony\Component\Form\FormEvents::POST_SUBMIT,
            function (\Symfony\Component\Form\Event\PostSubmitEvent $event) {
                $form = $event->getForm();
                $data = $form->getData();

                if (null === $data) {
                    return;
                }

                $promoPrice = $data->getPromoPrice();
                $promoStartsAt = $data->getPromoStartsAt();
                $promoEndsAt = $data->getPromoEndsAt();

                $hasAny = null !== $promoPrice || null !== $promoStartsAt || null !== $promoEndsAt;

                if ($hasAny) {
                    if (null === $promoPrice) {
                        $form->get('promoPrice')->addError(new \Symfony\Component\Form\FormError('Le prix promotionnel est requis.'));
                    }
                    if (null === $promoStartsAt) {
                        $form->get('promoStartsAt')->addError(new \Symfony\Component\Form\FormError('La date de début est requise.'));
                    }
                    if (null === $promoEndsAt) {
                        $form->get('promoEndsAt')->addError(new \Symfony\Component\Form\FormError('La date de fin est requise.'));
                    }
                }

                if (null !== $promoPrice && null !== $data->getPrice() && (float) $promoPrice >= $data->getPriceFloat()) {
                    $form->get('promoPrice')->addError(new \Symfony\Component\Form\FormError('Le prix promo doit être strictement inférieur au prix normal.'));
                }

                if (null !== $promoStartsAt && null !== $promoEndsAt && $promoEndsAt < $promoStartsAt) {
                    $form->get('promoEndsAt')->addError(new \Symfony\Component\Form\FormError('La date de fin doit être postérieure à la date de début.'));
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
