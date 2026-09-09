<?php

declare(strict_types=1);

namespace App\Tests\Functional;

/**
 * B-01 — RG-33 / UC-22: profile page, password change, email immutable.
 */
class AccountProfileTest extends FunctionalTestCase
{
    public function testAccountPageLoadsForAuthenticatedUser(): void
    {
        $this->login('client@example.com');

        $this->client->request('GET', '/account');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mon compte');
        $this->assertSelectorTextContains('body', 'client@example.com');
    }

    public function testAccountPageRedirectsAnonymous(): void
    {
        $this->client->request('GET', '/account');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('/login', $this->client->getResponse()->headers->get('Location'));
    }

    public function testProfileFormContainsCurrentData(): void
    {
        $this->login('client@example.com');

        $crawler = $this->client->request('GET', '/account');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form[name="profile"]'));
        $this->assertSelectorTextContains('form[name="profile"]', 'Modifier mon profil');
    }

    public function testEmailIsNotEditableInProfileForm(): void
    {
        $this->login('client@example.com');

        $crawler = $this->client->request('GET', '/account');

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $crawler->filter('form[name="profile"] input[type="email"]'));
    }

    public function testProfileCanBeUpdated(): void
    {
        $this->login('client@example.com');
        $crawler = $this->client->request('GET', '/account');

        $token = $crawler->filter('input[name="profile[_token]"]')->attr('value');

        $this->client->request('POST', '/account/profile', [
            'profile' => [
                'firstName' => 'Jean-Pierre',
                'lastName' => 'Dupont',
                'birthDate' => ['year' => '1990', 'month' => '5', 'day' => '15'],
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects('/account');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Jean-Pierre Dupont');

        $user = $this->findUser('client@example.com');
        $this->assertSame('Jean-Pierre', $user->getFirstName());
        $this->assertSame('Dupont', $user->getLastName());
        $this->assertSame('1990-05-15', $user->getBirthDate()->format('Y-m-d'));
    }

    public function testEmailRemainsUnchangedAfterProfileUpdate(): void
    {
        $this->login('client@example.com');
        $crawler = $this->client->request('GET', '/account');

        $token = $crawler->filter('input[name="profile[_token]"]')->attr('value');

        $this->client->request('POST', '/account/profile', [
            'profile' => [
                'firstName' => 'Nouveau',
                'lastName' => 'Nom',
                'birthDate' => ['year' => '1985', 'month' => '12', 'day' => '25'],
                '_token' => $token,
            ],
        ]);

        $user = $this->findUser('client@example.com');
        $this->assertSame('client@example.com', $user->getEmail());
    }

    public function testPasswordCanBeChangedWithCorrectOldPassword(): void
    {
        $this->login('client@example.com');
        $crawler = $this->client->request('GET', '/account');

        $token = $crawler->filter('input[name="change_password[_token]"]')->attr('value');

        $this->client->request('POST', '/account/password', [
            'change_password' => [
                'currentPassword' => 'password',
                'plainPassword' => [
                    'first' => 'NewP@ssw0rd!',
                    'second' => 'NewP@ssw0rd!',
                ],
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects('/account');
    }

    public function testPasswordChangeRejectedWithWrongOldPassword(): void
    {
        $this->login('client@example.com');
        $crawler = $this->client->request('GET', '/account');

        $token = $crawler->filter('input[name="change_password[_token]"]')->attr('value');

        $this->client->request('POST', '/account/password', [
            'change_password' => [
                'currentPassword' => 'wrongpassword',
                'plainPassword' => [
                    'first' => 'NewP@ssw0rd!',
                    'second' => 'NewP@ssw0rd!',
                ],
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects('/account');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Le mot de passe actuel est incorrect.');
    }
}
