<?php

namespace Bernskiold\LaravelCurrencyConverter\Concerns;

use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Automatically maintains a base-currency copy of one or more monetary columns.
 *
 * Define the columns to convert with a static $currencyConversions map on the
 * model, where each key is the raw amount column and the value is the column
 * that stores the converted (base currency) amount:
 *
 *     protected static array<string, string> $currencyConversions = [
 *         'amount' => 'amount_sek',
 *     ];
 */
trait ConvertsCurrencies
{
    protected static function bootConvertsCurrencies(): void
    {
        self::creating(function (self $model): void {
            $model->convertCurrencyAmounts();
        });

        self::updating(function (self $model): void {
            $model->convertCurrencyAmounts(onlyDirty: true);
        });
    }

    /**
     * @return array<string, string>
     */
    public static function currencyConversionMap(): array
    {
        return static::$currencyConversions;
    }

    /**
     * Recalculate every stored converted amount and persist without firing events.
     */
    public function recalculateCurrencyConversions(): void
    {
        $this->convertCurrencyAmounts();
        $this->saveQuietly();
    }

    /**
     * Populate the converted (base currency) amounts for the configured attributes.
     *
     * Conversion failures are logged but never block the save: the record is
     * persisted with the converted amount left untouched so it can be backfilled.
     */
    protected function convertCurrencyAmounts(bool $onlyDirty = false): void
    {
        $baseCurrency = CurrencyConverter::baseCurrency();
        $currency = $this->currencyCode();

        foreach (static::$currencyConversions as $rawKey => $convertedKey) {
            if ($onlyDirty && ! $this->isDirty([$rawKey, $this->currencyColumn()])) {
                continue;
            }

            if (empty($currency) || $currency === $baseCurrency) {
                $this->{$convertedKey} = $this->{$rawKey};

                continue;
            }

            if (! $this->{$rawKey}) {
                continue;
            }

            try {
                $this->{$convertedKey} = CurrencyConverter::toBase(
                    value: (float) $this->{$rawKey},
                    from: $currency,
                );
            } catch (Throwable $e) {
                Log::warning('Currency conversion failed; saving without converted amount.', [
                    'model' => static::class,
                    'id' => $this->getKey(),
                    'attribute' => $rawKey,
                    'from' => $currency,
                    'to' => $baseCurrency,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Format a raw amount with its own currency, e.g. "1,234.56 USD".
     */
    public function amountWithCurrency(string $key, ?int $decimals = null): string
    {
        if ($this->{$key} === null) {
            return '';
        }

        return CurrencyConverter::format((float) $this->{$key}, $this->currencyCode(), $decimals);
    }

    /**
     * Format the converted amount for a column in the base currency.
     *
     * Accepts either a raw column (resolved to its converted column via the
     * conversion map) or a converted column directly.
     */
    public function amountInBaseCurrency(string $key, ?int $decimals = null): string
    {
        $convertedKey = static::$currencyConversions[$key] ?? $key;

        if ($this->{$convertedKey} === null) {
            return '';
        }

        return CurrencyConverter::format((float) $this->{$convertedKey}, CurrencyConverter::baseCurrency(), $decimals);
    }

    /**
     * The currency code for this record. Override to read a different attribute.
     */
    public function currencyCode(): ?string
    {
        return $this->{$this->currencyColumn()};
    }

    /**
     * The attribute that stores this record's currency code.
     */
    protected function currencyColumn(): string
    {
        return 'currency';
    }
}
