<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Form\ReviewType;
use App\Service\ReviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReviewController extends AbstractController
{
    #[Route('/products/{id}/reviews', name: 'app_product_review', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Product $product, Request $request, ReviewService $reviewService, EntityManagerInterface $entityManager): Response
    {
        $review = new \App\Entity\Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user instanceof \App\Entity\User) {
                return $this->redirectToRoute('app_login');
            }

            try {
                $reviewService->create(
                    $user,
                    $product,
                    $review->getRating(),
                    $review->getComment(),
                );
                $this->addFlash('success', 'Votre avis a été publié.');
            } catch (\DomainException $e) {
                $this->addFlash('danger', 'Impossible de publier votre avis.');
            }
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }
}
