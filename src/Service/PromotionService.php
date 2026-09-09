<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;

class PromotionService
{
    public function getCurrentPrice(Product $product, ?\DateTimeInterface $now = null): float
    {
        if ($this->isOnPromotion($product, $now)) {
            return $product->getPromoPriceFloat() ?? $product->getPriceFloat();
        }

        return $product->getPriceFloat();
    }

    public function isOnPromotion(Product $product, ?\DateTimeInterface $now = null): bool
    {
        $promoPrice = $product->getPromoPrice();
        $startsAt = $product->getPromoStartsAt();
        $endsAt = $product->getPromoEndsAt();

        if (null === $promoPrice || null === $startsAt || null === $endsAt) {
            return false;
        }

        if ((float) $promoPrice >= $product->getPriceFloat()) {
            return false;
        }

        $now ??= new \DateTimeImmutable();

        if ($now < $startsAt || $now > $endsAt) {
            return false;
        }

        return true;
    }
}
