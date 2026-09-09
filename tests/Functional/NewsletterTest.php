<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;

/**
 * UC-28 / RG-39: newsletter opt-in at registration, toggle in profile, unsubscribe via token.
 */
class NewsletterTest extends FunctionalTestCase
{
    public function testNewsletterCheckboxIsUncheckedByDefault(): void
    {
        $crawler = $this->client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $checkbox = $crawler->filter('input[name="registration_form[newsletterSubscribed]"]');
        $this->assertCount(1, $checkbox);
        $this->assertNull($checkbox->attr('checked'));
    }

    public function testRegistrationWithNewsletterOptIn(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $token = $crawler->filter('input[name="registration_form[_token]"]')->attr('value');

        $this->client->request('POST', '/register', [
            'registration_form' => [
                'email' => 'newsletter_test@example.com',
                'firstName' => 'Test',
                'lastName' => 'User',
                'birthDate' => '1990-06-15',
                'plainPassword' => ['first' => 'SecurePass1!', 'second' => 'SecurePass1!'],
                'newsletterSubscribed' => true,
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $user = $this->repository(User::class)->findOneBy(['email' => 'newsletter_test@example.com']);
        $this->assertNotNull($user);
        $this->assertTrue($user->isNewsletterSubscribed());
        $this->assertNotNull($user->getNewsletterToken());
    }

    public function testRegistrationWithoutNewsletter(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $token = $crawler->filter('input[name="registration_form[_token]"]')->attr('value');

        $this->client->request('POST', '/register', [
            'registration_form' => [
                'email' => 'no_newsletter@example.com',
                'firstName' => 'No',
                'lastName' => 'Newsletter',
                'birthDate' => '1995-03-10',
                'plainPassword' => ['first' => 'SecurePass1!', 'second' => 'SecurePass1!'],
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $user = $this->repository(User::class)->findOneBy(['email' => 'no_newsletter@example.com']);
        $this->assertNotNull($user);
        $this->assertFalse($user->isNewsletterSubscribed());
        $this->assertNull($user->getNewsletterToken());
    }

    public function testToggleNewsletterInProfile(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');
        $this->assertFalse($user->isNewsletterSubscribed());

        $crawler = $this->client->request('GET', '/account');
        $form = $crawler->filter('form[action*="newsletter/toggle"]');
        $this->assertGreaterThan(0, $form->count(), 'Le formulaire de newsletter est introuvable.');

        $this->client->submit($form->form());

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'abonné à la newsletter');

        $this->entityManager()->clear();
        $user = $this->repository(User::class)->find($user->getId());
        $this->assertTrue($user->isNewsletterSubscribed());
        $this->assertNotNull($user->getNewsletterToken());
    }

    public function testUnsubscribeViaToken(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $user->setNewsletterSubscribed(true);
        $user->setNewsletterToken('test-unsubscribe-token-123');
        $this->entityManager()->flush();
        $this->entityManager()->clear();

        $this->client->request('GET', '/newsletter/unsubscribe/test-unsubscribe-token-123');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Désinscription confirmée');

        $this->entityManager()->clear();
        $user = $this->repository(User::class)->find($user->getId());
        $this->assertFalse($user->isNewsletterSubscribed());
        $this->assertNull($user->getNewsletterToken());
    }

    public function testUnsubscribeWithInvalidToken(): void
    {
        $this->client->request('GET', '/newsletter/unsubscribe/invalid-token-999');
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'invalide');
    }

    public function testUnsubscribeTokenIsRemovedAfterUse(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $user->setNewsletterSubscribed(true);
        $user->setNewsletterToken('single-use-token');
        $this->entityManager()->flush();
        $this->entityManager()->clear();

        $this->client->request('GET', '/newsletter/unsubscribe/single-use-token');
        $this->client->request('GET', '/newsletter/unsubscribe/single-use-token');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'invalide');
    }
}
