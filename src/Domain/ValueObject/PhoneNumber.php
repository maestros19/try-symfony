<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidPhoneNumberException;

/**
 * Value Object PhoneNumber
 * 
 * Démontre:
 * - Immutabilité (readonly)
 * - Auto-validation
 * - Encapsulation de la logique de formatage
 * - Protection des données
 */
final readonly class PhoneNumber
{
    private string $value;

    public function __construct(string $phoneNumber)
    {
        $normalized = $this->normalize($phoneNumber);
        
        if (!$this->isValid($normalized)) {
            throw InvalidPhoneNumberException::invalidFormat($phoneNumber);
        }
        
        $this->value = $normalized;
    }

    /**
     * Normalise le numéro (supprime espaces, tirets, parenthèses)
     */
    private function normalize(string $phoneNumber): string
    {
        // Supprime tous les caractères non numériques sauf le +
        $normalized = preg_replace('/[^\d+]/', '', trim($phoneNumber));
        
        // Si commence par 00, remplace par +
        if (str_starts_with($normalized, '00')) {
            $normalized = '+' . substr($normalized, 2);
        }
        
        return $normalized;
    }

    /**
     * Valide le format du numéro
     */
    private function isValid(string $phoneNumber): bool
    {
        // Vide
        if (empty($phoneNumber)) {
            return false;
        }

        // Format international: +XX... (10-15 chiffres)
        if (str_starts_with($phoneNumber, '+')) {
            return preg_match('/^\+\d{10,15}$/', $phoneNumber) === 1;
        }

        // Format national français: 10 chiffres commençant par 0
        if (strlen($phoneNumber) === 10 && str_starts_with($phoneNumber, '0')) {
            return preg_match('/^0[1-9]\d{8}$/', $phoneNumber) === 1;
        }

        // Autres formats nationaux (8-15 chiffres)
        return preg_match('/^\d{8,15}$/', $phoneNumber) === 1;
    }

    /**
     * Retourne le numéro brut (normalisé)
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Retourne le numéro formaté pour l'affichage
     */
    public function getFormatted(): string
    {
        // Format international
        if (str_starts_with($this->value, '+')) {
            return $this->formatInternational();
        }
        
        // Format français (10 chiffres)
        if (strlen($this->value) === 10 && str_starts_with($this->value, '0')) {
            return $this->formatFrench();
        }
        
        // Autres formats: retourne tel quel
        return $this->value;
    }

    /**
     * Formate un numéro français (XX XX XX XX XX)
     */
    private function formatFrench(): string
    {
        return sprintf(
            '%s %s %s %s %s',
            substr($this->value, 0, 2),
            substr($this->value, 2, 2),
            substr($this->value, 4, 2),
            substr($this->value, 6, 2),
            substr($this->value, 8, 2)
        );
    }

    /**
     * Formate un numéro international
     */
    private function formatInternational(): string
    {
        // Exemple: +33 6 12 34 56 78
        if (str_starts_with($this->value, '+33')) {
            $national = '0' . substr($this->value, 3);
            return '+33 ' . $this->formatFrench();
        }

        // Autres pays: +XX ... (groupes de 2-3 chiffres)
        $number = substr($this->value, 1); // Enlève le +
        $countryCode = substr($number, 0, 2);
        $rest = substr($number, 2);
        
        // Groupe par paquets de 3
        $formatted = '+' . $countryCode . ' ' . chunk_split($rest, 3, ' ');
        return trim($formatted);
    }

    /**
     * Retourne le type de numéro
     */
    public function getType(): string
    {
        if (str_starts_with($this->value, '+') || 
            (strlen($this->value) === 10 && !str_starts_with($this->value, '0'))) {
            return 'international';
        }

        if (strlen($this->value) !== 10) {
            return 'unknown';
        }

        // Numéros français
        $prefix = substr($this->value, 0, 2);
        
        return match ($prefix) {
            '01' => 'fixe_ile_de_france',
            '02' => 'fixe_nord_ouest',
            '03' => 'fixe_nord_est',
            '04' => 'fixe_sud_est',
            '05' => 'fixe_sud_ouest',
            '06', '07' => 'mobile',
            '09' => 'voip',
            '08' => 'special',
            default => 'unknown',
        };
    }

    /**
     * Vérifie si c'est un numéro mobile
     */
    public function isMobile(): bool
    {
        return in_array($this->getType(), ['mobile', 'international']);
    }

    /**
     * Retourne le pays (si détectable)
     */
    public function getCountry(): ?string
    {
        if (!str_starts_with($this->value, '+')) {
            return 'France'; // Par défaut
        }

        return match (true) {
            str_starts_with($this->value, '+33') => 'France',
            str_starts_with($this->value, '+1') => 'USA/Canada',
            str_starts_with($this->value, '+44') => 'Royaume-Uni',
            str_starts_with($this->value, '+49') => 'Allemagne',
            str_starts_with($this->value, '+32') => 'Belgique',
            str_starts_with($this->value, '+41') => 'Suisse',
            str_starts_with($this->value, '+34') => 'Espagne',
            str_starts_with($this->value, '+39') => 'Italie',
            default => null,
        };
    }

    /**
     * Convertit en format international
     */
    public function toInternational(string $defaultCountryCode = '+33'): string
    {
        if (str_starts_with($this->value, '+')) {
            return $this->value;
        }

        // Si commence par 0 (format français), remplace par code pays
        if (str_starts_with($this->value, '0')) {
            return $defaultCountryCode . substr($this->value, 1);
        }

        return $defaultCountryCode . $this->value;
    }

    /**
     * Compare deux numéros
     */
    public function equals(PhoneNumber $other): bool
    {
        // Compare les versions normalisées
        return $this->value === $other->value;
    }

    /**
     * Conversion en string (utilise le format formaté)
     */
    public function __toString(): string
    {
        return $this->getFormatted();
    }

    /**
     * Factory method pour créer depuis différents formats
     */
    public static function fromInternational(string $countryCode, string $nationalNumber): self
    {
        // Enlève le 0 initial si présent
        $national = ltrim($nationalNumber, '0');
        return new self($countryCode . $national);
    }

    /**
     * Crée un numéro masqué (pour l'affichage public)
     */
    public function getMasked(): string
    {
        $formatted = $this->getFormatted();
        $length = strlen($formatted);
        
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        // Masque les chiffres du milieu
        $visible = 4; // Garde 4 derniers caractères visibles
        $masked = substr($formatted, 0, -$visible);
        $masked = preg_replace('/\d/', '*', $masked);
        
        return $masked . substr($formatted, -$visible);
    }

    /**
     * Retourne les informations complètes
     */
    public function getInfo(): array
    {
        return [
            'raw' => $this->value,
            'formatted' => $this->getFormatted(),
            'type' => $this->getType(),
            'country' => $this->getCountry(),
            'isMobile' => $this->isMobile(),
            'international' => $this->toInternational(),
        ];
    }
}