<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\StockAlert;
use App\Entity\User;
use App\Repository\StockAlertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class StockAlertService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StockAlertRepository $stockAlertRepository,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function request(User $user, Product $product): void
    {
        if ($product->getStock() > 0) {
            throw new \DomainException('Ce produit est actuellement en stock.');
        }

        if ($this->stockAlertRepository->hasPendingAlert($user, $product)) {
            throw new \DomainException('Vous avez déjà une alerte en attente pour ce produit.');
        }

        $alert = new StockAlert($user, $product);
        $this->entityManager->persist($alert);
        $this->entityManager->flush();
    }

    public function process(): int
    {
        $alerts = $this->stockAlertRepository->findPendingForInStockProducts();
        $sent = 0;

        foreach ($alerts as $alert) {
            $this->sendNotification($alert);
            $alert->markSent();
            ++$sent;
        }

        if ($sent > 0) {
            $this->entityManager->flush();
        }

        return $sent;
    }

    public function hasPendingAlert(User $user, Product $product): bool
    {
        return $this->stockAlertRepository->hasPendingAlert($user, $product);
    }

    private function sendNotification(StockAlert $alert): void
    {
        $product = $alert->getProduct();
        $user = $alert->getUser();

        $email = (new TemplatedEmail())
            ->from('no-reply@ludo-shop.local')
            ->to($user->getEmail())
            ->subject('Le produit "'.$product->getName().'" est de retour en stock !')
            ->htmlTemplate('email/stock_alert.html.twig')
            ->context([
                'user' => $user,
                'product' => $product,
            ]);

        $this->mailer->send($email);
    }
}
