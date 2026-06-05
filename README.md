# Laravel Currency Converter

A small, driver-based currency conversion package for Laravel with built-in caching.

- **Driver based** — ship with Frankfurter (free, keyless, ECB mid-market rates), exchangerate.host (keyed), and a fixed/static driver for testing and offline use. Add your own with `extend()`.
- **Cached** — rates are cached per driver and currency pair (default 1 day) so you don't hit the provider on every conversion.
- **Resilient** — a single `CurrencyConversionException` for every failure mode, so callers can decide how to degrade.

## Installation

```bash
composer require bernskiold/laravel-currency-converter
```

Publish the config if you want to customise drivers, caching, or the base currency:

```bash
php artisan vendor:publish --tag=currency-converter-config
```

## Usage

```php
use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;

// Convert a value (rounded to the configured number of decimals).
CurrencyConverter::convert(100, 'USD', 'SEK'); // 1050.0

// Just the rate.
CurrencyConverter::rate('USD', 'SEK'); // 10.5

// Convert into the configured base currency.
CurrencyConverter::toBase(100, 'USD'); // uses currency-converter.base_currency

// Use a specific driver for a single call.
CurrencyConverter::convert(100, 'USD', 'SEK', driver: 'exchangerate_host');
```

The same methods are available by resolving the class from the container:

```php
app(\Bernskiold\LaravelCurrencyConverter\CurrencyConverter::class)->convert(100, 'USD', 'SEK');
```

## Drivers

Set the default driver in `config/currency-converter.php` (or via `CURRENCY_CONVERTER_DRIVER`):

| Driver              | Key required | Notes                                             |
|---------------------|--------------|---------------------------------------------------|
| `frankfurter`       | No           | ECB mid-market reference rates, updated daily.    |
| `exchangerate_host` | Yes          | Set `EXCHANGERATE_HOST_KEY`.                       |
| `fixed`             | No           | Static rates from config — great for tests.       |

### Registering a custom driver

```php
use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;
use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;

CurrencyConverter::extend('my-provider', fn () => new class implements ExchangeRateProvider {
    public function getRate(string $from, string $to): float
    {
        return 1.0;
    }
});
```

## Testing

Use the `fixed` driver (or `Http::fake()` with the HTTP drivers) to avoid network calls:

```php
config()->set('currency-converter.default', 'fixed');
config()->set('currency-converter.drivers.fixed.rates', ['USD' => ['SEK' => 10.0]]);

expect(CurrencyConverter::convert(100, 'USD', 'SEK'))->toBe(1000.0);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
