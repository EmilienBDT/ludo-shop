<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Command\FixturesCheckCommand;
use App\Entity\Product;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * F-08 — ENF-11: fixtures consistency check command.
 */
class FixturesCheckTest extends FunctionalTestCase
{
    private function getTester(): CommandTester
    {
        return new CommandTester(new FixturesCheckCommand(
            $this->entityManager(),
        ));
    }

    public function testCommandPassesOnValidFixtures(): void
    {
        $tester = $this->getTester();

        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Toutes les vérifications sont passées', $tester->getDisplay());
    }

    public function testCommandFailsOnMatureProductWithLowMinAge(): void
    {
        $product = $this->repository(Product::class)->findOneBy([]);
        $this->assertNotNull($product);

        $product->setIsMature(true);
        $product->setMinAge(14);
        $this->entityManager()->flush();
        $this->entityManager()->clear();

        $tester = $this->getTester();
        $exitCode = $tester->execute([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('mature', $tester->getDisplay());
    }

    public function testCommandReportsAllChecksInOutput(): void
    {
        $tester = $this->getTester();
        $tester->execute([]);

        $display = $tester->getDisplay();
        $this->assertStringContainsString('produits', $display);
        $this->assertStringContainsString('mature', $display);
        $this->assertStringContainsString('wishlist', $display);
        $this->assertStringContainsString('adresse', $display);
    }
}
