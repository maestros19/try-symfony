<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidEmailException;

/**
 * Value Object Email - Immutable et auto-validant
 * Démontre l'encapsulation et la protection des données
 */
final readonly class Email
{
    private string $value;

    public function __construct(string $email)
    {
        $normalizedEmail = strtolower(trim($email));
        
        if (!filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::invalidFormat($email);
        }
        
        $this->value = $normalizedEmail;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }
}