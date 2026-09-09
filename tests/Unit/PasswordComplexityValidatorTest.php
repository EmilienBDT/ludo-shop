<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\User;
use App\Validator\PasswordComplexity;
use App\Validator\PasswordComplexityValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class PasswordComplexityValidatorTest extends TestCase
{
    private const VALID = 'Abcd!2345';

    private PasswordComplexityValidator $validator;

    private ExecutionContextInterface $context;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new PasswordComplexityValidator();
        $this->validator->initialize($this->context);
    }

    public function testValidPasswordPasses(): void
    {
        $this->expectNoViolation();
        $this->validator->validate(self::VALID, new PasswordComplexity());
    }

    public function testTooShortPasswordIsRejected(): void
    {
        $this->expectViolation();
        $this->validator->validate('Ab1!', new PasswordComplexity());
    }

    public function testMissingUppercaseIsRejected(): void
    {
        $this->expectViolation();
        $this->validator->validate('abcd!2345', new PasswordComplexity());
    }

    public function testMissingDigitIsRejected(): void
    {
        $this->expectViolation();
        $this->validator->validate('Abcd!efgh', new PasswordComplexity());
    }

    public function testMissingSpecialCharacterIsRejected(): void
    {
        $this->expectViolation();
        $this->validator->validate('Abcd12345', new PasswordComplexity());
    }

    public function testPasswordContainingEmailIsRejected(): void
    {
        $user = new User();
        $user->setEmail('jean.dupont@example.com');

        $constraint = new PasswordComplexity(user: $user);
        $this->expectViolation();
        $this->validator->validate('jean.dupont@example.com!2024', $constraint);
    }

    public function testNonStringValueIsRejected(): void
    {
        $this->expectNoViolation();
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedValueException::class);
        $this->validator->validate(12345, new PasswordComplexity());
    }

    public function testNullValuePasses(): void
    {
        $this->expectNoViolation();
        $this->validator->validate(null, new PasswordComplexity());
    }

    public function testWrongConstraintTypeThrows(): void
    {
        $this->expectNoViolation();
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate(self::VALID, new \Symfony\Component\Validator\Constraints\Length(min: 8));
    }

    private function expectViolation(): void
    {
        $builder = $this->createStub(ConstraintViolationBuilderInterface::class);
        $builder->method('setParameter')->willReturnSelf();
        $builder->method('setInvalidValue')->willReturnSelf();

        $this->context->expects($this->once())->method('buildViolation')->willReturn($builder);
    }

    private function expectNoViolation(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
    }
}
