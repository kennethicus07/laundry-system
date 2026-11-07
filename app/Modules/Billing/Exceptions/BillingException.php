<?php

namespace App\Modules\Billing\Exceptions;

use App\Modules\Billing\DTO\InvoiceBalance;
use DomainException;

/**
 * Business-rule violation in the Billing module.
 * Extends DomainException so existing catch blocks still work.
 */
class BillingException extends DomainException
{
    public static function activeInvoiceExists(string $invoiceNumber): self
    {
        return new self("An active invoice ({$invoiceNumber}) already exists for this order.");
    }

    public static function invoiceCreationInProgress(): self
    {
        return new self('An invoice for this order is already being created. Please try again.');
    }

    public static function invoiceIsVoid(): self
    {
        return new self('This invoice is void.');
    }

    public static function amountMustBePositive(): self
    {
        return new self('Payment must be greater than zero.');
    }

    public static function amountExceedsPayable(InvoiceBalance $balance): self
    {
        $message = "Payment exceeds the amount payable ({$balance->payable()->format()}).";

        if ($balance->pending->isPositive()) {
            $message .= " {$balance->pending->format()} is still awaiting QR verification.";
        }

        return new self($message);
    }

    public static function idempotencyKeyReused(): self
    {
        return new self('This request key was already used for a different invoice.');
    }
}