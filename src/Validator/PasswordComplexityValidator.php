<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PasswordComplexityValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PasswordComplexity) {
            throw new UnexpectedTypeException($constraint, PasswordComplexity::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $rules = [
            ['tooShort', static fn (): bool => strlen($value) >= $constraint->minLength, $constraint->tooShortMessage],
            ['missingUppercase', static fn (): bool => (bool) preg_match('/[A-Z]/', $value), $constraint->missingUppercaseMessage],
            ['missingDigit', static fn (): bool => (bool) preg_match('/[0-9]/', $value), $constraint->missingDigitMessage],
            ['missingSpecial', static fn (): bool => (bool) preg_match('/[^A-Za-z0-9]/', $value), $constraint->missingSpecialMessage],
        ];

        foreach ($rules as [$code, $check, $message]) {
            if (!$check()) {
                $this->context->buildViolation($message)
                    ->setParameter('{{ minLength }}', (string) $constraint->minLength)
                    ->setCode($code)
                    ->addViolation();

                return;
            }
        }

        $user = $constraint->user;
        if (null !== $user && $user->getEmail() && str_contains(strtolower($value), strtolower($user->getEmail()))) {
            $this->context->buildViolation($constraint->containsEmailMessage)
                ->setCode(PasswordComplexity::CONTAINS_EMAIL)
                ->addViolation();
        }
    }
}
