<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use App\Service\PromotionService;
use App\Service\StockAlertService;
use App\Service\WishlistService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/wishlist')]
class WishlistController extends AbstractController
{
    public function __construct(
        private readonly WishlistService $wishlistService,
        private readonly CartService $cartService,
        private readonly StockAlertService $stockAlertService,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[Route('', name: 'app_wishlist', methods: ['GET'])]
    public function index(PromotionService $promotionService): Response
    {
        $user = $this->getUserOrThrow();
        $items = $this->wishlistService->findByUser($user);
        $products = array_map(static fn ($item) => $item->getProduct(), $items);

        $cart = $this->cartService->getOrCreateCart($user);
        $cartProductIds = array_map(
            static fn ($item) => $item->getProduct()->getId(),
            iterator_to_array($cart->getItems()),
        );

        $inStockProducts = [];
        $inCartProducts = [];
        $outOfStockProducts = [];
        $stockAlertProductIds = [];

        foreach ($products as $product) {
            if ($product->getStock() <= 0) {
                $outOfStockProducts[] = $product;
                if ($this->stockAlertService->hasPendingAlert($user, $product)) {
                    $stockAlertProductIds[] = $product->getId();
                }
            } elseif (in_array($product->getId(), $cartProductIds, true)) {
                $inCartProducts[] = $product;
            } else {
                $inStockProducts[] = $product;
            }
        }

        return $this->render('wishlist/index.html.twig', [
            'items' => $items,
            'products' => $products,
            'inStockProducts' => $inStockProducts,
            'inCartProducts' => $inCartProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'wishlistProductIds' => array_map(
                static fn ($item) => $item->getProduct()->getId(),
                $items,
            ),
            'stockAlertProductIds' => $stockAlertProductIds,
            'promotionService' => $promotionService,
        ]);
    }

    #[Route('/add/{id}', name: 'app_wishlist_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function add(Product $product): Response
    {
        try {
            $this->wishlistService->add($this->getUserOrThrow(), $product);
            $this->addFlash('success', 'Produit ajouté à votre liste de souhaits.');
        } catch (\DomainException $e) {
            $this->addFlash('danger', $e->getMessage());

            return $this->redirectToRoute('app_catalog');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    #[Route('/remove/{id}', name: 'app_wishlist_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function remove(Product $product): Response
    {
        try {
            $this->wishlistService->remove($this->getUserOrThrow(), $product);
            $this->addFlash('success', 'Produit retiré de votre liste de souhaits.');
        } catch (\DomainException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        $request = $this->requestStack->getCurrentRequest();
        $referer = $request?->headers->get('referer');
        if ($referer && str_contains($referer, '/wishlist')) {
            return $this->redirectToRoute('app_wishlist');
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    #[Route('/toggle/{id}', name: 'app_wishlist_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggle(Product $product): JsonResponse
    {
        $user = $this->getUserOrThrow();

        try {
            if ($this->wishlistService->has($user, $product)) {
                $this->wishlistService->remove($user, $product);

                return new JsonResponse([
                    'added' => false,
                    'productName' => $product->getName(),
                ]);
            }

            $this->wishlistService->add($user, $product);

            return new JsonResponse([
                'added' => true,
                'productName' => $product->getName(),
            ]);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/add-all-to-cart', name: 'app_wishlist_add_all_to_cart', methods: ['POST'])]
    public function addAllToCart(): Response
    {
        $user = $this->getUserOrThrow();
        $items = $this->wishlistService->findByUser($user);
        $cart = $this->cartService->getOrCreateCart($user);
        $added = 0;

        foreach ($items as $item) {
            $product = $item->getProduct();
            if ($product->isAvailable()) {
                try {
                    $this->cartService->addProduct($cart, $product);
                    ++$added;
                } catch (\DomainException) {
                }
            }
        }

        if ($added > 0) {
            $this->addFlash('success', sprintf('%d produit(s) ajouté(s) au panier.', $added));
        } else {
            $this->addFlash('danger', 'Aucun produit disponible n\'a pu être ajouté au panier.');
        }

        return $this->redirectToRoute('app_wishlist');
    }

    private function getUserOrThrow(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('User not authenticated.');
        }

        return $user;
    }
}
