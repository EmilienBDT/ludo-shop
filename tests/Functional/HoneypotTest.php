<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;

/**
 * Covers RG-48 / UC-37: a filled honeypot field silently rejects the bot
 * registration (no account, no visible error), while legitimate registrations
 * still work.
 */
class HoneypotTest extends FunctionalTestCase
{
    public function testFilledHoneypotSilentlyRejectsRegistration(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => 'bot@example.com',
            'registration_form[firstName]' => 'Bot',
            'registration_form[lastName]' => 'Bot',
            'registration_form[birthDate]' => '1995-05-05',
            'registration_form[plainPassword][first]' => 'Tr0pSecur!sé',
            'registration_form[plainPassword][second]' => 'Tr0pSecur!sé',
            'registration_form[website]' => 'https://spam.example.com',
        ]);

        $this->assertResponseRedirects('http://localhost/login');
        $this->assertSame(0, $this->countUsers('bot@example.com'), 'No account must be created for bots');
    }

    public function testNoVisibleErrorForFilledHoneypot(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => 'bot2@example.com',
            'registration_form[firstName]' => 'Bot',
            'registration_form[lastName]' => 'Bot',
            'registration_form[birthDate]' => '1995-05-05',
            'registration_form[website]' => 'spam',
        ]);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-success', 'créé');
        $this->assertStringNotContainsString('erreur', $crawler->filter('.alert-danger')->count() > 0 ? $crawler->filter('.alert-danger')->text() : '');
    }

    public function testLegitimateRegistrationStillWorks(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => 'human@example.com',
            'registration_form[firstName]' => 'Human',
            'registration_form[lastName]' => 'Being',
            'registration_form[birthDate]' => '1995-05-05',
            'registration_form[plainPassword][first]' => 'Tr0pSecur!sé',
            'registration_form[plainPassword][second]' => 'Tr0pSecur!sé',
        ]);

        $this->assertResponseRedirects('http://localhost/login');
        $this->assertSame(1, $this->countUsers('human@example.com'));
    }

    public function testHoneypotFieldIsHiddenFromHumans(): void
    {
        $crawler = $this->client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('input[name="registration_form[website]"]')->count());
        $this->assertSame('off', $crawler->filter('input[name="registration_form[website]"]')->attr('autocomplete'));
    }

    private function countUsers(string $email): int
    {
        return $this->repository(User::class)->count(['email' => $email]);
    }
}
