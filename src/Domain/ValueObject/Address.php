<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidAddressException;

/**
 * Value Object Address
 * 
 * Démontre:
 * - Immutabilité (readonly)
 * - Auto-validation
 * - Encapsulation de données complexes
 * - Logique métier (formatage, validation)
 */
final readonly class Address
{
    private string $street;
    private string $city;
    private string $postalCode;
    private string $country;

    public function __construct(
        string $street,
        string $city,
        string $postalCode,
        string $country = 'France'
    ) {
        $this->validateStreet($street);
        $this->validateCity($city);
        $this->validatePostalCode($postalCode, $country);
        $this->validateCountry($country);
        
        $this->street = trim($street);
        $this->city = ucwords(strtolower(trim($city))); // Capitalise chaque mot
        $this->postalCode = trim($postalCode);
        $this->country = ucfirst(trim($country));
    }

    /**
     * Validation de la rue
     */
    private function validateStreet(string $street): void
    {
        $trimmed = trim($street);
        
        if (empty($trimmed)) {
            throw InvalidAddressException::emptyStreet();
        }

        if (strlen($trimmed) < 5) {
            throw new InvalidAddressException(
                'La rue doit contenir au moins 5 caractères'
            );
        }

        if (strlen($trimmed) > 255) {
            throw new InvalidAddressException(
                'La rue ne peut pas dépasser 255 caractères'
            );
        }
    }

    /**
     * Validation de la ville
     */
    private function validateCity(string $city): void
    {
        $trimmed = trim($city);
        
        if (empty($trimmed)) {
            throw InvalidAddressException::emptyCity();
        }

        if (strlen($trimmed) < 2) {
            throw new InvalidAddressException(
                'La ville doit contenir au moins 2 caractères'
            );
        }

        if (strlen($trimmed) > 100) {
            throw new InvalidAddressException(
                'La ville ne peut pas dépasser 100 caractères'
            );
        }

        // Validation: uniquement lettres, espaces, tirets, apostrophes
        if (!preg_match('/^[\p{L}\s\-\']+$/u', $trimmed)) {
            throw new InvalidAddressException(
                'La ville contient des caractères invalides'
            );
        }
    }

    /**
     * Validation du code postal (selon le pays)
     */
    private function validatePostalCode(string $postalCode, string $country): void
    {
        $normalized = trim($postalCode);
        
        if (empty($normalized)) {
            throw InvalidAddressException::emptyPostalCode();
        }

        // Validation selon le pays
        $isValid = match (strtolower($country)) {
            'france' => $this->isValidFrenchPostalCode($normalized),
            'belgique', 'belgium' => $this->isValidBelgianPostalCode($normalized),
            'suisse', 'switzerland' => $this->isValidSwissPostalCode($normalized),
            'canada' => $this->isValidCanadianPostalCode($normalized),
            'usa', 'états-unis' => $this->isValidUSPostalCode($normalized),
            default => strlen($normalized) >= 3 && strlen($normalized) <= 10
        };

        if (!$isValid) {
            throw InvalidAddressException::invalidPostalCode($postalCode);
        }
    }

    /**
     * Valide un code postal français (5 chiffres)
     */
    private function isValidFrenchPostalCode(string $code): bool
    {
        return preg_match('/^\d{5}$/', $code) === 1;
    }

    /**
     * Valide un code postal belge (4 chiffres)
     */
    private function isValidBelgianPostalCode(string $code): bool
    {
        return preg_match('/^\d{4}$/', $code) === 1;
    }

    /**
     * Valide un code postal suisse (4 chiffres)
     */
    private function isValidSwissPostalCode(string $code): bool
    {
        return preg_match('/^\d{4}$/', $code) === 1;
    }

    /**
     * Valide un code postal canadien (A1A 1A1)
     */
    private function isValidCanadianPostalCode(string $code): bool
    {
        return preg_match('/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/i', $code) === 1;
    }

    /**
     * Valide un ZIP code américain (12345 ou 12345-6789)
     */
    private function isValidUSPostalCode(string $code): bool
    {
        return preg_match('/^\d{5}(-\d{4})?$/', $code) === 1;
    }

    /**
     * Validation du pays
     */
    private function validateCountry(string $country): void
    {
        $trimmed = trim($country);
        
        if (empty($trimmed)) {
            throw InvalidAddressException::emptyCountry();
        }

        if (strlen($trimmed) < 2) {
            throw new InvalidAddressException(
                'Le pays doit contenir au moins 2 caractères'
            );
        }
    }

    /**
     * Retourne la rue
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * Retourne la ville
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Retourne le code postal
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * Retourne le pays
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Retourne l'adresse complète formatée
     */
    public function getFullAddress(): string
    {
        return sprintf(
            '%s, %s %s, %s',
            $this->street,
            $this->postalCode,
            $this->city,
            $this->country
        );
    }

    /**
     * Retourne l'adresse sur plusieurs lignes
     */
    public function getMultilineAddress(): string
    {
        return sprintf(
            "%s\n%s %s\n%s",
            $this->street,
            $this->postalCode,
            $this->city,
            $this->country
        );
    }

    /**
     * Retourne l'adresse courte (sans pays)
     */
    public function getShortAddress(): string
    {
        return sprintf(
            '%s, %s %s',
            $this->street,
            $this->postalCode,
            $this->city
        );
    }

    /**
     * Retourne le département (pour France)
     */
    public function getDepartment(): ?string
    {
        if ($this->country !== 'France') {
            return null;
        }

        return substr($this->postalCode, 0, 2);
    }

    /**
     * Retourne la région (pour France - simplifiée)
     */
    public function getRegion(): ?string
    {
        if ($this->country !== 'France') {
            return null;
        }

        $dept = $this->getDepartment();
        
        return match ($dept) {
            '75' => 'Île-de-France',
            '13' => 'Provence-Alpes-Côte d\'Azur',
            '69' => 'Auvergne-Rhône-Alpes',
            '33' => 'Nouvelle-Aquitaine',
            '59' => 'Hauts-de-France',
            '44' => 'Pays de la Loire',
            '31' => 'Occitanie',
            '35' => 'Bretagne',
            '67', '68' => 'Grand Est',
            default => 'Autre région',
        };
    }

    /**
     * Vérifie si l'adresse est dans une grande ville
     */
    public function isInMajorCity(): bool
    {
        $majorCities = [
            'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice',
            'Nantes', 'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille'
        ];

        foreach ($majorCities as $majorCity) {
            if (stripos($this->city, $majorCity) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare deux adresses
     */
    public function equals(Address $other): bool
    {
        return $this->street === $other->street
            && $this->city === $other->city
            && $this->postalCode === $other->postalCode
            && $this->country === $other->country;
    }

    /**
     * Compare uniquement la ville
     */
    // public function isSameCity(Address $other): bool
    // {
    //     return $this->city === $other->city
    //         && $this->postalCode === $other->postalCode;
    // }

    /**
     * Conversion en string (utilise getFullAddress)
     */
    public function __toString(): string
    {
        return $this->getFullAddress();
    }

    /**
     * Retourne un tableau pour l'API
     */
    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
            'fullAddress' => $this->getFullAddress(),
        ];
    }

    /**
     * Factory method pour créer depuis un tableau
     * 
     * @param array<string, string> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'] ?? $data['rue'] ?? '',
            city: $data['city'] ?? $data['ville'] ?? '',
            postalCode: $data['postalCode'] ?? $data['postal_code'] ?? $data['codePostal'] ?? '',
            country: $data['country'] ?? $data['pays'] ?? 'France',
        );
    }

    /**
     * Génère une URL Google Maps
     */
    public function getGoogleMapsUrl(): string
    {
        $address = urlencode($this->getFullAddress());
        return "https://www.google.com/maps/search/?api=1&query={$address}";
    }

    /**
     * Retourne les coordonnées approximatives (simulation)
     * En production, utiliser une API de géocodage
     */
    public function getApproximateCoordinates(): array
    {
        // Coordonnées approximatives selon le code postal (France)
        if ($this->country === 'France' && strlen($this->postalCode) === 5) {
            $dept = substr($this->postalCode, 0, 2);
            
            // Coordonnées très approximatives (centre du département)
            $coords = match ($dept) {
                '75' => ['lat' => 48.8566, 'lng' => 2.3522], // Paris
                '13' => ['lat' => 43.2965, 'lng' => 5.3698], // Marseille
                '69' => ['lat' => 45.7640, 'lng' => 4.8357], // Lyon
                '33' => ['lat' => 44.8378, 'lng' => -0.5792], // Bordeaux
                '59' => ['lat' => 50.6292, 'lng' => 3.0573], // Lille
                default => ['lat' => 46.2276, 'lng' => 2.2137], // Centre France
            };

            return $coords;
        }

        return ['lat' => null, 'lng' => null];
    }

    /**
     * Calcule la distance approximative avec une autre adresse (en km)
     * Utilise la formule de Haversine
     */
    public function getDistanceTo(Address $other): ?float
    {
        $coords1 = $this->getApproximateCoordinates();
        $coords2 = $other->getApproximateCoordinates();

        if ($coords1['lat'] === null || $coords2['lat'] === null) {
            return null;
        }

        $earthRadius = 6371; // km

        $latDelta = deg2rad($coords2['lat'] - $coords1['lat']);
        $lngDelta = deg2rad($coords2['lng'] - $coords1['lng']);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($coords1['lat'])) * cos(deg2rad($coords2['lat'])) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }


    // Mehtodes statis


        /**
     * Extrait la rue depuis une adresse complète
     */
    public static function extractStreet(string $fullAddress): ?string
    {
        // Exemple attendu : "10 Rue de la Paix, 75002 Paris, France"
        $parts = explode(',', $fullAddress);

        // Rue = première partie
        return isset($parts[0]) ? trim($parts[0]) : null;
    }

    /**
     * Extrait le code postal depuis une adresse complète
     */
    public static function extractPostalCode(string $fullAddress): ?string
    {
        // On cherche un code postal français, belge, suisse, canadien ou US
        if (preg_match('/\b\d{5}\b/', $fullAddress, $matches)) {
            return $matches[0]; // Code postal français ou US 5 chiffres
        }

        if (preg_match('/\b\d{4}\b/', $fullAddress, $matches)) {
            return $matches[0]; // Belgique ou Suisse
        }

        if (preg_match('/[A-Z]\d[A-Z]\s?\d[A-Z]\d/i', $fullAddress, $matches)) {
            return strtoupper($matches[0]); // Canada
        }

        if (preg_match('/\b\d{5}-\d{4}\b/', $fullAddress, $matches)) {
            return $matches[0]; // US ZIP+4
        }

        return null;
    }

    /**
     * Extrait la ville depuis une adresse complète
     */
    public static function extractCity(string $fullAddress): ?string
    {
        // Exemple : "10 Rue de la Paix, 75002 Paris, France"
        // On supprime la rue et le pays pour isoler le bloc central
        $parts = array_map('trim', explode(',', $fullAddress));

        if (count($parts) < 2) {
            return null;
        }

        // Généralement, la deuxième partie contient "75002 Paris"
        $middlePart = $parts[1];

        // On essaie d’extraire le nom de la ville après le code postal
        if (preg_match('/\b\d{4,5}\s+([\p{L}\s\'\-]+)/u', $middlePart, $matches)) {
            return ucwords(strtolower(trim($matches[1])));
        }

        // Sinon, on tente de deviner (si la partie ne contient pas de chiffres)
        if (!preg_match('/\d/', $middlePart)) {
            return ucwords(strtolower($middlePart));
        }

        return null;
    }

    /**
     * Crée une instance Address à partir d'une adresse complète sous forme de chaîne
     *
     * Exemple : "10 Rue de la Paix, 75002 Paris, France"
     */
    public static function fromFullAddress(string $fullAddress): self
    {
        $street = self::extractStreet($fullAddress) ?? '';
        $postalCode = self::extractPostalCode($fullAddress) ?? '';
        $city = self::extractCity($fullAddress) ?? '';
        
        // Tentative de déduction du pays (dernière partie après la dernière virgule)
        $parts = array_map('trim', explode(',', $fullAddress));
        $country = count($parts) > 2 ? ucfirst(strtolower(end($parts))) : 'France';

        return new self(
            street: $street,
            city: $city,
            postalCode: $postalCode,
            country: $country
        );
    }

}