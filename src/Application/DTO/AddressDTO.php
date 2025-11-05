<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entity\Owner;
use DateTimeImmutable;
use JsonSerializable;

/**
 * DTO pour l'adresse
 */
final readonly class AddressDTO
{
    public function __construct(
        public string $street,
        public string $city,
        public string $postalCode,
        public string $country,
        public string $fullAddress,
    ) {
    }

    public static function fromValueObject(\App\Domain\ValueObject\Address $address): self
    {
        return new self(
            street: $address->getStreet(),
            city: $address->getCity(),
            postalCode: $address->getPostalCode(),
            country: $address->getCountry(),
            fullAddress: $address->getFullAddress(),
        );
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
            'fullAddress' => $this->fullAddress,
        ];
    }
}
