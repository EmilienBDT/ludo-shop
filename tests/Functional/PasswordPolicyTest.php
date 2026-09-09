<?php

declare(strict_types=1);

namespace App\Tests\Functional;

/**
 * Covers UC-35 / RG-46: weak passwords are rejected at registration, the
 * password must not contain the user email, strong passwords are accepted.
 */
class PasswordPolicyTest extends FunctionalTestCase
{
    public function testWeakPasswordIsRejectedAtRegistration(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => 'new.user@example.com',
            'registration_form[firstName]' => 'Nouveau',
            'registration_form[lastName]' => 'Client',
            'registration_form[birthDate]' => '1995-05-05',
            'registration_form[plainPassword][first]' => 'weakpassword',
            'registration_form[plainPassword][second]' => 'weakpassword',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('body', 'une majuscule');
        $this->assertSame(0, $this->countUsers('new.user@example.com'), 'No account must be created');
    }

    public function testPasswordContainingEmailIsRejectedAtRegistration(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => 'johndoe@example.com',
            'registration_form[firstName]' => 'John',
            'registration_form[lastName]' => 'Doe',
            'registration_form[birthDate]' => '1995-05-05',
            'registration_form[plainPassword][first]' => 'Johndoe@example.com!123',
            'registration_form[plainPassword][second]' => 'Johndoe@example.com!123',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('body', 'ne doit pas contenir votre adresse e-mail');
        $this->assertSame(0, $this->countUsers('johndoe@example.com'));
    }

    public function testStrongPasswordIsAcceptedAtRegistration(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Créer mon compte', [
            'registration_form[email]' => 'strong.user@example.com',
            'registration_form[firstName]' => 'Strong',
            'registration_form[lastName]' => 'User',
            'registration_form[birthDate]' => '1995-05-05',
            'registration_form[plainPassword][first]' => 'Tr0pSecur!sé',
            'registration_form[plainPassword][second]' => 'Tr0pSecur!sé',
        ]);

        $this->assertResponseRedirects('http://localhost/login');
        $this->assertSame(1, $this->countUsers('strong.user@example.com'));
    }

    private function countUsers(string $email): int
    {
        return $this->repository(\App\Entity\User::class)->count(['email' => $email]);
    }
}
