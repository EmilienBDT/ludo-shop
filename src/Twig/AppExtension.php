<?php

namespace App\Twig;

use App\Entity\User;
use App\Service\CartService;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly Security $security,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cart_count', $this->getCartCount(...)),
        ];
    }

    public function getCartCount(): int
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return 0;
        }

        return $this->cartService->countForUser($user);
    }
}
