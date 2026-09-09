<?php

declare(strict_types=1);

namespace App\Tests\Functional;

/**
 * Covers RG-49 / UC-38: the confirmation token is single-use and login is
 * reserved to verified emails.
 */
class EmailVerificationTest extends FunctionalTestCase
{
    public function testRegistrationSendsVerificationToken(): void
    {
        $this->register('fresh.user@example.com');

        $user = $this->findUser('fresh.user@example.com');
        $this->assertNotNull($user);
        $this->assertNotNull($user->getEmailVerifyToken(), 'Verification token must be generated at registration');
        $this->assertFalse($user->isEmailVerified());
    }

    public function testUnverifiedEmailCannotLogin(): void
    {
        $this->register('fresh.user@example.com');

        $this->client->request('GET', '/logout');
        $this->loginThroughForm('fresh.user@example.com', 'Tr0pSecur!sé');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'confirmer votre adresse');

        $this->client->request('GET', '/account');
        $this->assertResponseRedirects('http://localhost/login');
    }

    public function testVerifyTokenIsSingleUse(): void
    {
        $this->register('fresh.user@example.com');
        $user = $this->findUser('fresh.user@example.com');
        $token = $user->getEmailVerifyToken();

        $this->client->request('GET', '/verify-email/'.$token);
        $this->assertResponseRedirects('http://localhost/login');

        $user = $this->findUser('fresh.user@example.com');
        $this->assertTrue($user->isEmailVerified());
        $this->assertNull($user->getEmailVerifyToken(), 'Token must be invalidated after use');

        $this->client->request('GET', '/verify-email/'.$token);
        $this->assertResponseRedirects('http://localhost/login');
        $this->assertNull($user->getEmailVerifyToken());
    }

    public function testVerifiedEmailCanLogin(): void
    {
        $this->register('fresh.user@example.com');
        $user = $this->findUser('fresh.user@example.com');
        $this->client->request('GET', '/verify-email/'.$user->getEmailVerifyToken());

        $this->client->request('GET', '/logout');
        $this->loginThroughForm('fresh.user@example.com', 'Tr0pSecur!sé');

        $this->assertResponseRedirects('http://localhost/');
    }

    public function testResendEmailCreatesNewSingleUseToken(): void
    {
        $this->register('fresh.user@example.com');
        $user = $this->findUser('fresh.user@example.com');
        $oldToken = $user->getEmailVerifyToken();

        $this->client->request('POST', '/verify-email/resend', ['email' => 'fresh.user@example.com']);

        $user = $this->findUser('fresh.user@example.com');
        $this->assertNotNull($user->getEmailVerifyToken());
        $this->assertNotSame($oldToken, $user->getEmailVerifyToken(), 'A new token must be generated');
    }

    private function register(string $email): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => $email,
            'registration_form[firstName]' => 'Fresh',
            'registration_form[lastName]' => 'User',
            'registration_form[birthDate]' => '1995-05-05',
            'registration_form[plainPassword][first]' => 'Tr0pSecur!sé',
            'registration_form[plainPassword][second]' => 'Tr0pSecur!sé',
        ]);
    }

    private function loginThroughForm(string $email, string $password): void
    {
        $crawler = $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            '_username' => $email,
            '_password' => $password,
            '_csrf_token' => $crawler->filter('input[name="_csrf_token"]')->attr('value'),
        ]);
    }
}
