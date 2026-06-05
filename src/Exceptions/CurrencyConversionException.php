<?php

namespace Bernskiold\LaravelCurrencyConverter\Exceptions;

use Exception;
use Throwable;

class CurrencyConversionException extends Exception
{
    public static function requestFailed(string $driver, string $from, string $to, ?Throwable $previous = null): self
    {
        return new self(
            "The [{$driver}] driver failed to fetch the exchange rate from {$from} to {$to}.",
            previous: $previous,
        );
    }

    public static function missingRate(string $driver, string $from, string $to): self
    {
        return new self(
            "The [{$driver}] driver did not return an exchange rate from {$from} to {$to}.",
        );
    }

    public static function missingApiKey(string $driver): self
    {
        return new self(
            "The [{$driver}] driver is missing its API key. Please set the relevant access key in your configuration.",
        );
    }
}
