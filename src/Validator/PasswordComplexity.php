<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PasswordComplexity extends Constraint
{
    public const TOO_SHORT = 'password_too_short';
    public const MISSING_UPPERCASE = 'password_missing_uppercase';
    public const MISSING_DIGIT = 'password_missing_digit';
    public const MISSING_SPECIAL = 'password_missing_special';
    public const CONTAINS_EMAIL = 'password_contains_email';

    public string $tooShortMessage = 'Le mot de passe doit contenir au moins {{ minLength }} caractères.';

    public string $missingUppercaseMessage = 'Le mot de passe doit contenir au moins une majuscule.';

    public string $missingDigitMessage = 'Le mot de passe doit contenir au moins un chiffre.';

    public string $missingSpecialMessage = 'Le mot de passe doit contenir au moins un caractère spécial.';

    public string $containsEmailMessage = 'Le mot de passe ne doit pas contenir votre adresse e-mail.';

    #[HasNamedArguments]
    public function __construct(
        public readonly int $minLength = 8,
        public readonly ?User $user = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
