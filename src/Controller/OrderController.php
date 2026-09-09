<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Form\CheckoutFormType;
use App\Service\AddressService;
use App\Service\CartService;
use App\Service\InvoiceService;
use App\Service\OrderMailerService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
        private readonly InvoiceService $invoiceService,
        private readonly AddressService $addressService,
        private readonly OrderMailerService $orderMailerService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/checkout', name: 'app_checkout', methods: ['GET'])]
    public function checkout(): Response
    {
        $user = $this->getCurrentUser();
        $cart = $this->cartService->getOrCreateCart($user);
        if (0 === $cart->getItems()->count()) {
            $this->addFlash('warning', 'Votre panier est vide.');

            return $this->redirectToRoute('app_cart');
        }

        $defaultAddress = $this->addressService->getDefault($user);
        $form = $this->createForm(CheckoutFormType::class, null, [
            'default_address' => $defaultAddress,
        ]);

        return $this->render('order/checkout.html.twig', [
            'cart' => $cart,
            'total' => $this->cartService->getTotal($cart),
            'form' => $form,
            'addresses' => $this->addressService->findByUser($user),
        ]);
    }

    #[Route('/checkout', name: 'app_checkout_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $user = $this->getCurrentUser();
        $cart = $this->cartService->getOrCreateCart($user);
        $defaultAddress = $this->addressService->getDefault($user);
        $form = $this->createForm(CheckoutFormType::class, null, [
            'default_address' => $defaultAddress,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $order = $this->orderService->createFromCart(
                    $cart,
                    $data['addressLine'],
                    $data['city'],
                    $data['postalCode'],
                    $data['country'],
                );

                return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('order/checkout.html.twig', [
            'cart' => $cart,
            'total' => $this->cartService->getTotal($cart),
            'form' => $form,
            'addresses' => $this->addressService->findByUser($user),
        ]);
    }

    #[Route('/orders', name: 'app_orders', methods: ['GET'])]
    public function index(): Response
    {
        $orders = $this->entityManager->getRepository(Order::class)->findBy(
            ['user' => $this->getCurrentUser()],
            ['createdAt' => 'DESC'],
        );

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/orders/{id}', name: 'app_order_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Order $order): Response
    {
        $this->assertOwnsOrder($order);

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/orders/{id}/pay', name: 'app_order_pay', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function pay(Order $order): Response
    {
        $this->assertOwnsOrder($order);

        try {
            $this->orderService->pay($order);
            $this->invoiceService->createForOrder($order);
            $this->orderMailerService->sendOrderConfirmation($order);
            $this->addFlash('success', 'Paiement simulé accepté. Votre facture est disponible.');
        } catch (\DomainException $e) {
            $this->addFlash('danger', $e->getMessage());
        } catch (\LogicException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
    }

    #[Route('/orders/{id}/cancel', name: 'app_order_cancel', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function cancel(Order $order): Response
    {
        $this->assertOwnsOrder($order);

        try {
            $this->orderService->cancel($order);
            $this->addFlash('success', 'Votre commande a été annulée.');
        } catch (\LogicException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
    }

    private function assertOwnsOrder(Order $order): void
    {
        if ($order->getUser() !== $this->getCurrentUser()) {
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
