<?php

declare(strict_types=1);

namespace App\Tests\Functional;

/**
 * D-01 — RG-40 / UC-29: admin dashboard with revenue, orders, top products, low stock.
 */
class AdminDashboardTest extends FunctionalTestCase
{
    public function testDashboardAccessibleForAdmin(): void
    {
        $this->login('admin@example.com');

        $this->client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testDashboardDeniedForClient(): void
    {
        $this->login('client@example.com');

        $this->client->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDashboardShowsRevenueCards(): void
    {
        $this->login('admin@example.com');

        $this->client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Chiffre d\'affaires');
        $this->assertSelectorTextContains('body', 'Commandes payées');
    }
}
