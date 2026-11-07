<?php

namespace App\Modules\Billing\Support;

use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Immutable money value stored as integer centavos.
 * All Billing comparisons and totals go through this class: no floats.
 */
final readonly class Money implements JsonSerializable, Stringable
{
    public const CURRENCY_SYMBOL = '₱';
    public const DECIMAL_PLACES = 2;

    private const SCALE = 100;

    private function __construct(
        public int $centavos,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Factories
    |--------------------------------------------------------------------------
    */

    public static function zero(): self
    {
        return new self(0);
    }

    public static function ofCentavos(int $centavos): self
    {
        return new self($centavos);
    }

    /**
     * Accepts DB decimal strings ("450.00"), request input ("450.5"),
     * whole pesos (450), or floats (converted via a 2-decimal string).
     */
    public static function of(self|string|int|float $amount): self
    {
        if ($amount instanceof self) {
            return $amount;
        }

        if (is_int($amount)) {
            return new self($amount * self::SCALE);
        }

        if (is_float($amount)) {
            if (! is_finite($amount)) {
                throw new InvalidArgumentException('Money amount must be a finite number.');
            }

            $amount = number_format($amount, self::DECIMAL_PLACES, '.', '');
        }

        $amount = trim($amount);

        if (! preg_match('/^(-)?(\d+)(?:\.(\d+))?$/', $amount, $m)) {
            throw new InvalidArgumentException("Invalid money amount [{$amount}].");
        }

        $negative = $m[1] === '-';
        $fraction = $m[3] ?? '';

        // Allow "450.5000" but reject "450.555".
        if (strlen($fraction) > self::DECIMAL_PLACES
            && rtrim(substr($fraction, self::DECIMAL_PLACES), '0') !== '') {
            throw new InvalidArgumentException(
                "Money amount [{$amount}] has more than ".self::DECIMAL_PLACES.' decimal places.'
            );
        }

        $fraction = str_pad(substr($fraction, 0, self::DECIMAL_PLACES), self::DECIMAL_PLACES, '0');
        $centavos = ((int) $m[2]) * self::SCALE + (int) $fraction;

        return new self($negative ? -$centavos : $centavos);
    }

    /**
     * @param  iterable<self|string|int|float>  $amounts
     */
    public static function sum(iterable $amounts): self
    {
        $total = 0;

        foreach ($amounts as $amount) {
            $total += self::of($amount)->centavos;
        }

        return new self($total);
    }

    /*
    |--------------------------------------------------------------------------
    | Arithmetic
    |--------------------------------------------------------------------------
    */

    public function plus(self|string|int $other): self
    {
        return new self($this->centavos + self::of($other)->centavos);
    }

    public function minus(self|string|int $other): self
    {
        return new self($this->centavos - self::of($other)->centavos);
    }

    public function atLeastZero(): self
    {
        return $this->centavos < 0 ? self::zero() : $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Comparisons
    |--------------------------------------------------------------------------
    */

    public function isZero(): bool
    {
        return $this->centavos === 0;
    }

    public function isPositive(): bool
    {
        return $this->centavos > 0;
    }

    public function isNegative(): bool
    {
        return $this->centavos < 0;
    }

    public function equals(self|string|int $other): bool
    {
        return $this->centavos === self::of($other)->centavos;
    }

    public function greaterThan(self|string|int $other): bool
    {
        return $this->centavos > self::of($other)->centavos;
    }

    public function greaterThanOrEqual(self|string|int $other): bool
    {
        return $this->centavos >= self::of($other)->centavos;
    }

    public function lessThan(self|string|int $other): bool
    {
        return $this->centavos < self::of($other)->centavos;
    }

    public function lessThanOrEqual(self|string|int $other): bool
    {
        return $this->centavos <= self::of($other)->centavos;
    }

    /*
    |--------------------------------------------------------------------------
    | Output
    |--------------------------------------------------------------------------
    */

    /** "1234.50": the format to store in decimal(12,2) columns. */
    public function toDecimal(): string
    {
        $abs = abs($this->centavos);

        return ($this->centavos < 0 ? '-' : '')
            .intdiv($abs, self::SCALE)
            .'.'
            .str_pad((string) ($abs % self::SCALE), self::DECIMAL_PLACES, '0', STR_PAD_LEFT);
    }

    /** "₱1,234.50": for messages and display. */
    public function format(): string
    {
        $abs = abs($this->centavos);

        return ($this->centavos < 0 ? '-' : '')
            .self::CURRENCY_SYMBOL
            .number_format(intdiv($abs, self::SCALE))
            .'.'
            .str_pad((string) ($abs % self::SCALE), self::DECIMAL_PLACES, '0', STR_PAD_LEFT);
    }

    public function jsonSerialize(): string
    {
        return $this->toDecimal();
    }

    public function __toString(): string
    {
        return $this->toDecimal();
    }
}