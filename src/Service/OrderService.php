<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CartService $cartService,
        private readonly OrderStatusService $orderStatusService,
    ) {
    }

    public function createFromCart(
        Cart $cart,
        string $addressLine,
        string $city,
        string $postalCode,
        string $country,
        float $vatRate = 0.20,
    ): Order {
        if (0 === $cart->getItems()->count()) {
            throw new \DomainException('Votre panier est vide.');
        }

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            if (!$product->isActive() || $product->getStock() < $item->getQuantity()) {
                throw new \DomainException(sprintf('Le produit « %s » n\'est plus disponible en quantité suffisante.', $product->getName()));
            }
        }

        $order = new Order(
            number: $this->generateNumber(),
            user: $cart->getUser(),
            addressLine: $addressLine,
            city: $city,
            postalCode: $postalCode,
            country: $country,
        );
        $order->setVatRate($vatRate * 100);

        $total = 0.0;
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $orderItem = new OrderItem(
                order: $order,
                product: $product,
                productName: $product->getName(),
                unitPrice: $item->getUnitPrice(),
                quantity: $item->getQuantity(),
            );
            $order->addItem($orderItem);
            $total += $item->getLineTotal();
        }
        $order->setTotalAmount(round($total, 2));

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $product->setStock($product->getStock() - $item->getQuantity());
        }

        $this->entityManager->persist($order);
        $this->cartService->clear($cart);
        $this->entityManager->flush();

        return $order;
    }

    public function pay(Order $order): void
    {
        $this->orderStatusService->transition($order, OrderStatus::Paid, $order->getUser());
        $order->setPaidAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function cancel(Order $order): void
    {
        $this->orderStatusService->transition($order, OrderStatus::Cancelled, $order->getUser());
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            if (null !== $product) {
                $product->setStock($product->getStock() + $item->getQuantity());
            }
        }
        $this->entityManager->flush();
    }

    private function generateNumber(): string
    {
        return sprintf(
            'CMD-%s-%s',
            (new \DateTimeImmutable())->format('YmdHis'),
            strtoupper(bin2hex(random_bytes(3))),
        );
    }
}
