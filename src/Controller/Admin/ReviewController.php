<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/reviews')]
class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly ReviewService $reviewService,
    ) {
    }

    #[Route('', name: 'app_admin_reviews', methods: ['GET'])]
    public function index(): Response
    {
        $reviews = $this->reviewRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/review/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_admin_review_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggle(Review $review): Response
    {
        $this->reviewService->toggleVisibility($review);

        $status = $review->isHidden() ? 'masqué' : 'visible';
        $this->addFlash('success', sprintf('Avis %s.', $status));

        return $this->redirectToRoute('app_admin_reviews');
    }
}
