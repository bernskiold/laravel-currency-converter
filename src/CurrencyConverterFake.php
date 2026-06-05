<?php

namespace Bernskiold\LaravelCurrencyConverter;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Closure;
use PHPUnit\Framework\Assert;

class CurrencyConverterFake extends CurrencyConverter
{
    /**
     * @var array<int, array{value: float, from: string, to: string, driver: string|null}>
     */
    protected array $conversions = [];

    /**
     * @param  array<string, array<string, float|int>>  $rates
     */
    public function __construct(
        protected array $rates = [],
        protected string $base = 'SEK',
        protected float $defaultRate = 1.0,
    ) {
        // Intentionally does not call parent::__construct(): the fake overrides
        // every public method and never touches the real dependencies.
    }

    public function rate(string $from, string $to, ?string $driver = null): float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return 1.0;
        }

        return (float) ($this->rates[$from][$to] ?? $this->defaultRate);
    }

    public function convert(float $value, string $from, string $to, ?string $driver = null): float
    {
        $this->conversions[] = [
            'value' => $value,
            'from' => strtoupper($from),
            'to' => strtoupper($to),
            'driver' => $driver,
        ];

        return round($value * $this->rate($from, $to, $driver), 2);
    }

    public function baseCurrency(): string
    {
        return strtoupper($this->base);
    }

    public function format(float $value, ?string $currency = null, ?int $decimals = null): string
    {
        $number = number_format($value, $decimals ?? 2, '.', ',');

        return $currency ? "{$number} {$currency}" : $number;
    }

    public function driver(?string $driver = null): ExchangeRateProvider
    {
        throw new CurrencyConversionException('Exchange rate drivers are not available on the CurrencyConverter fake.');
    }

    public function extend(string $driver, Closure $callback): static
    {
        return $this;
    }

    /**
     * Assert that at least one matching conversion was recorded.
     *
     * Pass a from/to currency pair (either may be null to match any), or a
     * closure receiving (float $value, string $from, string $to, ?string $driver).
     */
    public function assertConverted(Closure|string|null $from = null, ?string $to = null): void
    {
        $count = count($this->recorded($from, $to));

        Assert::assertGreaterThan(0, $count, 'Expected a matching currency conversion, but none was recorded.');
    }

    public function assertConvertedTimes(int $times, Closure|string|null $from = null, ?string $to = null): void
    {
        $count = count($this->recorded($from, $to));

        Assert::assertSame($times, $count, "Expected {$times} matching currency conversion(s), but {$count} were recorded.");
    }

    public function assertNothingConverted(): void
    {
        Assert::assertCount(0, $this->conversions, 'Expected no currency conversions, but some were recorded.');
    }

    /**
     * @return array<int, array{value: float, from: string, to: string, driver: string|null}>
     */
    public function recorded(Closure|string|null $from = null, ?string $to = null): array
    {
        if ($from instanceof Closure) {
            return array_values(array_filter(
                $this->conversions,
                fn (array $c): bool => $from($c['value'], $c['from'], $c['to'], $c['driver']),
            ));
        }

        return array_values(array_filter($this->conversions, function (array $c) use ($from, $to): bool {
            if ($from !== null && $c['from'] !== strtoupper($from)) {
                return false;
            }

            if ($to !== null && $c['to'] !== strtoupper($to)) {
                return false;
            }

            return true;
        }));
    }
}
