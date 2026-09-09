<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\ReviewType;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Service\PromotionService;
use App\Service\ReviewService;
use App\Service\StockAlertService;
use App\Service\WishlistService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_catalog')]
    public function index(ProductRepository $productRepository, PromotionService $promotionService, WishlistService $wishlistService, Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 9;
        $includeMature = $this->isMatureAllowed();

        $products = $productRepository->findActivePaginated($limit, ($page - 1) * $limit, $includeMature);
        $total = $productRepository->countActive($includeMature);
        $maxPage = max(1, (int) ceil($total / $limit));

        return $this->render('catalog/index.html.twig', [
            'products' => $products,
            'page' => $page,
            'maxPage' => $maxPage,
            'criteria' => [],
            'promotionService' => $promotionService,
            'wishlistProductIds' => $this->getWishlistProductIds($wishlistService),
        ]);
    }

    #[Route('/products/search', name: 'app_catalog_search', methods: ['GET'])]
    public function search(ProductRepository $productRepository, PromotionService $promotionService, WishlistService $wishlistService, Request $request): Response
    {
        $criteria = [
            'q' => $this->nullableString($request, 'q'),
            'publisher' => $this->nullableString($request, 'publisher'),
            'price_min' => $this->nullableString($request, 'price_min'),
            'price_max' => $this->nullableString($request, 'price_max'),
            'min_age' => $this->nullableInt($request, 'min_age'),
            'max_age' => $this->nullableInt($request, 'max_age'),
            'min_players' => $this->nullableInt($request, 'min_players'),
            'max_players' => $this->nullableInt($request, 'max_players'),
            'max_playtime' => $this->nullableInt($request, 'max_playtime'),
        ];

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 9;
        $includeMature = $this->isMatureAllowed();

        $products = $productRepository->search($criteria, $includeMature, $limit, ($page - 1) * $limit);
        $total = $productRepository->countSearch($criteria, $includeMature);
        $maxPage = max(1, (int) ceil($total / $limit));

        return $this->render('catalog/index.html.twig', [
            'products' => $products,
            'page' => $page,
            'maxPage' => $maxPage,
            'criteria' => $criteria,
            'promotionService' => $promotionService,
            'wishlistProductIds' => $this->getWishlistProductIds($wishlistService),
        ]);
    }

    #[Route('/products/fragment', name: 'app_catalog_fragment', methods: ['GET'])]
    public function fragment(ProductRepository $productRepository, PromotionService $promotionService, WishlistService $wishlistService, Request $request): Response
    {
        $criteria = [
            'q' => $this->nullableString($request, 'q'),
            'publisher' => $this->nullableString($request, 'publisher'),
            'price_min' => $this->nullableString($request, 'price_min'),
            'price_max' => $this->nullableString($request, 'price_max'),
            'min_age' => $this->nullableInt($request, 'min_age'),
            'max_age' => $this->nullableInt($request, 'max_age'),
            'min_players' => $this->nullableInt($request, 'min_players'),
            'max_players' => $this->nullableInt($request, 'max_players'),
            'max_playtime' => $this->nullableInt($request, 'max_playtime'),
        ];

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 9;
        $includeMature = $this->isMatureAllowed();

        $products = $productRepository->search($criteria, $includeMature, $limit, ($page - 1) * $limit);

        return $this->render('catalog/_product_grid.html.twig', [
            'products' => $products,
            'promotionService' => $promotionService,
            'wishlistProductIds' => $this->getWishlistProductIds($wishlistService),
        ]);
    }

    #[Route('/products/{id}', name: 'app_product_show', requirements: ['id' => '\d+'])]
    public function show(
        Product $product,
        ReviewRepository $reviewRepository,
        ReviewService $reviewService,
        PromotionService $promotionService,
        WishlistService $wishlistService,
        StockAlertService $stockAlertService,
    ): Response {
        if (!$product->isActive()) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        if ($product->isMature() && !$this->isMatureAllowed()) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $reviews = $reviewRepository->findVisibleByProduct($product);
        $averageRating = $reviewService->averageRating($product);

        $userReview = null;
        $canReview = false;
        $isInWishlist = false;
        $hasStockAlert = false;
        if (null !== ($user = $this->getUser()) && $user instanceof User) {
            $canReview = $reviewService->canReview($user, $product)
                && !$reviewRepository->hasUserReviewedProduct($user, $product);
            if (!$canReview) {
                $userReview = $reviewRepository->findOneBy(['user' => $user, 'product' => $product]);
            }
            $isInWishlist = $wishlistService->has($user, $product);
            $hasStockAlert = $stockAlertService->hasPendingAlert($user, $product);
        }

        $reviewForm = $this->createForm(ReviewType::class, null, [
            'action' => $this->generateUrl('app_product_review', ['id' => $product->getId()]),
            'method' => 'POST',
        ]);

        return $this->render('catalog/show.html.twig', [
            'product' => $product,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'canReview' => $canReview,
            'userReview' => $userReview,
            'reviewForm' => $reviewForm,
            'currentPrice' => $promotionService->getCurrentPrice($product),
            'isOnPromotion' => $promotionService->isOnPromotion($product),
            'isInWishlist' => $isInWishlist,
            'hasStockAlert' => $hasStockAlert,
        ]);
    }

    private function nullableString(Request $request, string $key): ?string
    {
        $value = $request->query->get($key);
        if (null === $value || '' === trim((string) $value)) {
            return null;
        }

        return trim((string) $value);
    }

    private function nullableInt(Request $request, string $key): ?int
    {
        $value = $this->nullableString($request, $key);

        return null === $value ? null : (int) $value;
    }

    private function isMatureAllowed(): bool
    {
        $user = $this->getUser();

        return $user instanceof User && $user->isAdult();
    }

    /**
     * @return list<int>
     */
    private function getWishlistProductIds(WishlistService $wishlistService): array
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($item) => $item->getProduct()->getId(),
            $wishlistService->findByUser($user),
        )));
    }
}
