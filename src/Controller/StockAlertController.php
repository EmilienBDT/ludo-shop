<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Service\StockAlertService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StockAlertController extends AbstractController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    #[Route('/products/{id}/stock-alert', name: 'app_stock_alert', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function subscribe(Product $product, StockAlertService $stockAlertService): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $stockAlertService->request($user, $product);
            $this->addFlash('success', 'Vous serez notifié(e) quand ce produit sera de retour en stock.');
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
}
