<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;

class InvoiceService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function createForOrder(Order $order): Invoice
    {
        if (OrderStatus::Paid !== $order->getStatus()) {
            throw new \DomainException('La facture ne peut être émise que pour une commande payée.');
        }
        if (null !== $order->getInvoice()) {
            throw new \DomainException('Une facture existe déjà pour cette commande.');
        }

        $invoice = new Invoice(
            number: $this->generateNumber(),
            order: $order,
            user: $order->getUser(),
            totalHt: $order->getTotalHt(),
            totalTtc: $order->getTotalAmount(),
            vatRate: $order->getVatRate(),
        );

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $invoice;
    }

    private function generateNumber(): string
    {
        $year = (new \DateTimeImmutable())->format('Y');
        $repo = $this->entityManager->getRepository(Invoice::class);
        $counter = (int) $repo->count([]);

        do {
            ++$counter;
            $number = sprintf('FAC-%s-%06d', $year, $counter);
        } while (null !== $repo->findOneBy(['number' => $number]));

        return $number;
    }
}
