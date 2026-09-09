<?php

namespace App\Controller;

use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class InvoiceController extends AbstractController
{
    #[Route('/orders/{id}/invoice', name: 'app_invoice_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Order $order): Response
    {
        if ($order->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $invoice = $order->getInvoice();
        if (null === $invoice) {
            $this->addFlash('warning', 'Aucune facture disponible pour cette commande.');

            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
        }

        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
            'order' => $order,
        ]);
    }

    #[Route('/orders/{id}/invoice/print', name: 'app_invoice_print', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function print(Order $order): Response
    {
        if ($order->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $invoice = $order->getInvoice();
        if (null === $invoice) {
            $this->addFlash('warning', 'Aucune facture disponible pour cette commande.');

            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
        }

        return $this->render('invoice/print.html.twig', [
            'invoice' => $invoice,
            'order' => $order,
        ]);
    }
}
