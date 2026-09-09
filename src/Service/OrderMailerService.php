<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class OrderMailerService
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
    }

    /**
     * RG-37: send order confirmation after payment with frozen data, one email per paid order.
     */
    public function sendOrderConfirmation(Order $order): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@ludo-shop.local')
            ->to($order->getUser()->getEmail())
            ->subject('Confirmation de votre commande n° '.$order->getNumber())
            ->htmlTemplate('email/order_confirmation.html.twig')
            ->context([
                'order' => $order,
                'items' => $order->getItems(),
                'user' => $order->getUser(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * RG-38: send status notification for each status reached, never on pending.
     */
    public function sendStatusNotification(Order $order): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@ludo-shop.local')
            ->to($order->getUser()->getEmail())
            ->subject('Commande n°'.$order->getNumber().' — '.$order->getStatus()->label())
            ->htmlTemplate('email/order_status.html.twig')
            ->context([
                'order' => $order,
                'user' => $order->getUser(),
            ]);

        $this->mailer->send($email);
    }
}
