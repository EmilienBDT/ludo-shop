<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    #[Route('/cart', name: 'app_cart')]
    public function show(): Response
    {
        $cart = $this->cartService->getOrCreateCart($this->getCurrentUser());

        return $this->render('cart/show.html.twig', [
            'cart' => $cart,
            'total' => $this->cartService->getTotal($cart),
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request): Response
    {
        if ($product->isMature() && !$this->getCurrentUser()->isAdult()) {
            $this->addFlash('danger', 'Ce produit est réservé aux adultes (+18).');
        } elseif (!$product->isAvailable()) {
            $this->addFlash('danger', 'Ce produit n\'est pas disponible.');
        } else {
            try {
                $this->cartService->addProduct($this->cartService->getOrCreateCart($this->getCurrentUser()), $product);
                $this->addFlash('success', sprintf('« %s » a été ajouté à votre panier.', $product->getName()));
            } catch (\DomainException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?? $this->generateUrl('app_product_show', ['id' => $product->getId()]));
    }

    #[Route('/cart/items/{id}/update', name: 'app_cart_update', methods: ['POST'])]
    public function updateQuantity(CartItem $item, Request $request): Response
    {
        $this->assertOwnsItem($item);
        $quantity = max(0, $request->request->getInt('quantity', 1));

        try {
            $this->cartService->setQuantity($item->getCart(), $item, $quantity);
        } catch (\DomainException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/items/{id}/update-json', name: 'app_cart_update_json', methods: ['POST'])]
    public function updateQuantityJson(CartItem $item, Request $request): Response
    {
        $this->assertOwnsItem($item);

        $raw = $request->request->get('quantity');
        if (!is_numeric($raw) || (int) $raw != $raw || (int) $raw < 0) {
            return $this->json(['ok' => false, 'error' => 'Quantité invalide.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $quantity = (int) $raw;

        try {
            $this->cartService->setQuantity($item->getCart(), $item, $quantity);
        } catch (\DomainException $e) {
            return $this->json(['ok' => false, 'error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (0 === $quantity) {
            return $this->json([
                'ok' => true,
                'removed' => true,
                'total' => $this->cartService->getTotal($item->getCart()),
                'itemCount' => $this->cartService->getItemCount($item->getCart()),
            ]);
        }

        return $this->json([
            'ok' => true,
            'lineTotal' => $item->getLineTotal(),
            'total' => $this->cartService->getTotal($item->getCart()),
            'itemCount' => $this->cartService->getItemCount($item->getCart()),
        ]);
    }

    #[Route('/cart/items/{id}/remove', name: 'app_cart_remove', methods: ['POST'])]
    public function removeItem(CartItem $item): Response
    {
        $this->assertOwnsItem($item);
        $this->cartService->removeItem($item->getCart(), $item);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(): Response
    {
        $this->cartService->clear($this->cartService->getOrCreateCart($this->getCurrentUser()));

        return $this->redirectToRoute('app_cart');
    }

    private function assertOwnsItem(CartItem $item): void
    {
        if ($item->getCart()->getUser() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException();
        }
    }

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
