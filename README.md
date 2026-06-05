# Convert currencies in Laravel, the easy way

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bernskiold/laravel-currency-converter.svg?style=flat-square)](https://packagist.org/packages/bernskiold/laravel-currency-converter)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/bernskiold/laravel-currency-converter/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/bernskiold/laravel-currency-converter/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/bernskiold/laravel-currency-converter/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/bernskiold/laravel-currency-converter/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/bernskiold/laravel-currency-converter.svg?style=flat-square)](https://packagist.org/packages/bernskiold/laravel-currency-converter)

Working with money in more than one currency shouldn't be painful. This package gives you a clean, expressive way to convert between currencies, look up exchange rates, and keep a base-currency copy of your model's amounts in sync — all backed by pluggable providers and sensible caching.

```php
use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;

CurrencyConverter::convert(100, 'USD', 'SEK'); // 1050.0
CurrencyConverter::rate('USD', 'SEK');         // 10.5
CurrencyConverter::toBase(100, 'USD');         // into your app's base currency
```

It ships with a free, keyless provider out of the box, so you can be up and running in a minute — and when you're ready for something else, swapping providers is a one-line change.

## Why you'll like it

- **Batteries included.** [Frankfurter](https://frankfurter.dev) (free, keyless, European Central Bank reference rates) works without any setup.
- **Driver based.** Frankfurter, exchangerate.host, and a fixed/static driver come built in — and adding your own takes a single method.
- **Cached by default.** Rates are cached per provider and currency pair (a day by default), so you're not hitting an API on every conversion.
- **Forgiving.** Every failure mode throws a single, catchable `CurrencyConversionException`, so you decide how to degrade — never the library.
- **Model friendly.** A small trait keeps a base-currency column in sync automatically, with display helpers for your views.

## Installation

You can install the package via Composer:

```bash
composer require bernskiold/laravel-currency-converter
```

That's it — the free Frankfurter driver is active by default. If you'd like to tweak the providers, caching, base currency, or number formatting, publish the config:

```bash
php artisan vendor:publish --tag=currency-converter-config
```

## Usage

Reach for the facade anywhere in your app:

```php
use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;

// Convert a value (rounded to the configured number of decimals).
CurrencyConverter::convert(100, 'USD', 'SEK'); // 1050.0

// Just the rate, please.
CurrencyConverter::rate('USD', 'SEK'); // 10.5

// Convert into — or out of — your configured base currency.
CurrencyConverter::toBase(100, 'USD');   // USD -> base_currency
CurrencyConverter::fromBase(100, 'USD'); // base_currency -> USD

// Need a specific provider for a single call? Say so.
CurrencyConverter::convert(100, 'USD', 'SEK', driver: 'exchangerate_host');
```

Prefer dependency injection over facades? Resolve the class straight from the container — same methods, no facade required:

```php
app(\Bernskiold\LaravelCurrencyConverter\CurrencyConverter::class)->convert(100, 'USD', 'SEK');
```

### Formatting amounts

When it's time to show a number to a human, `format()` uses your configured formatting (US conventions by default):

```php
CurrencyConverter::format(1234.5);        // "1,234.50"
CurrencyConverter::format(1234.5, 'USD'); // "1,234.50 USD"
```

### Automatic currency conversion on your models

Often you'll store an amount in whatever currency the record was created in, but you also want a copy of that amount in your reporting currency so totals and comparisons are easy. The `ConvertsCurrencies` trait keeps that copy in sync for you — every time the model is saved.

Add the trait and tell it which columns to convert with a `$currencyConversions` map. Each entry maps a **source column** (the amount in the record's own currency) to a **target column** (where the base-currency value should be stored):

```php
use Bernskiold\LaravelCurrencyConverter\Concerns\ConvertsCurrencies;

class Expense extends Model
{
    use ConvertsCurrencies;

    protected static array $currencyConversions = [
        'amount' => 'amount_sek',
    ];
}
```

You can convert as many columns as you like — just add more entries to the map.

#### What your model needs

The trait makes a few small assumptions:

- **A currency column.** It reads the record's currency from a `currency` attribute by default (an ISO code such as `USD`).
- **The source and target columns exist.** Both the amount column and its base-currency counterpart must be real database columns. A migration for the example above would look like:

  ```php
  $table->string('currency', 3)->default('SEK');
  $table->decimal('amount', 12, 2)->nullable();
  $table->decimal('amount_sek', 12, 2)->nullable();
  ```

That's it — no other configuration is required on the model.

#### Using a different currency column

If your currency lives somewhere other than a `currency` column, override `currencyColumn()`:

```php
protected function currencyColumn(): string
{
    return 'currency_code';
}
```

Need the currency from somewhere that isn't a plain column — a relationship, say? Override `currencyCode()` instead, which is what the trait actually calls (and which is public, so it's handy in your own code too):

```php
public function currencyCode(): ?string
{
    return $this->billingAccount->currency;
}
```

#### How it behaves

- On **create and update**, each target column is filled using `toBase()` (on update, only when the amount or currency actually changed).
- If the record is already in the **base currency**, the amount is copied across as-is — no API call.
- If a conversion ever **fails**, it's logged rather than thrown, so the save always goes through. You can fill in any gaps later:

  ```php
  $expense->recalculateCurrencyConversions();
  ```

#### Display helpers

The trait also gives your views a couple of friendly helpers, both formatted with your configured [number formatting](#formatting-amounts):

```php
$expense->amountWithCurrency('amount');    // "1,234.56 USD"
$expense->amountInBaseCurrency('amount');  // "12,962.88 SEK"
```

## Choosing a provider

Set your default provider in `config/currency-converter.php` (or via the `CURRENCY_CONVERTER_DRIVER` environment variable):

| Driver               | Key required | Notes                                                                 |
|----------------------|--------------|-----------------------------------------------------------------------|
| `frankfurter`        | No           | ECB mid-market reference rates, updated daily. The default.           |
| `exchangerate_api`   | Optional     | Broad coverage. Uses your key if set (`EXCHANGERATE_API_KEY`), otherwise the free keyless endpoint. |
| `exchangerate_host`  | Yes          | Set `EXCHANGERATE_HOST_KEY`.                                           |
| `open_exchange_rates`| Yes          | Set `OPEN_EXCHANGE_RATES_APP_ID`. Free plan is USD-base only.          |
| `fixer`              | Yes          | Set `FIXER_ACCESS_KEY`. Free plan is EUR-base only.                    |
| `database`           | No           | Read rates from a table you manage. See below.                        |
| `fixed`              | No           | Static rates from config — great for tests.                           |

#### The database driver

The `database` driver reads rates from a table you control — handy when you need pinned, auditable rates rather than live market data. Publish and run the migration:

```bash
php artisan vendor:publish --tag=currency-converter-migrations
php artisan migrate
```

This creates an `exchange_rates` table (`from_currency`, `to_currency`, `rate`). The table and column names are configurable under `currency-converter.drivers.database`.

A convenience `ExchangeRate` model is included for managing the rates — from a scheduled job, an importer, or by hand:

```php
use Bernskiold\LaravelCurrencyConverter\Models\ExchangeRate;

ExchangeRate::setRate('USD', 'SEK', 10.42); // creates or updates the pair

ExchangeRate::forPair('USD', 'SEK')->value('rate'); // 10.42
```

The model reads its table, connection, and column names from the same config, so it stays in step with the driver.

### Bringing your own provider

Need rates from somewhere we don't support yet? Write a class that implements the `ExchangeRateProvider` contract:

```php
namespace App\CurrencyConverter;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Illuminate\Support\Facades\Http;

class AcmeBankDriver implements ExchangeRateProvider
{
    public function getRate(string $from, string $to): float
    {
        return (float) Http::acmeBank()
            ->get("/rates/{$from}/{$to}")
            ->json('rate');
    }
}
```

Then register it — usually in a service provider's `boot()` method — and select it via config (`currency-converter.default`) or per call (`driver: 'acme-bank'`):

```php
use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;
use App\CurrencyConverter\AcmeBankDriver;

CurrencyConverter::extend('acme-bank', fn () => new AcmeBankDriver);
```

The closure receives the container, so feel free to resolve any dependencies your driver needs.

## Testing

The easiest way to test code that converts currencies is to fake the converter. `CurrencyConverter::fake()` swaps in a fake that returns predictable rates and never touches the network — and records every conversion so you can assert against it:

```php
use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;

CurrencyConverter::fake(['USD' => ['SEK' => 10.0]]);

// ... exercise your code ...

CurrencyConverter::assertConverted('USD', 'SEK');
```

Any currency pair you don't define converts 1:1, so a bare `CurrencyConverter::fake()` is enough when you only care that conversion happened. The fake also drives the `ConvertsCurrencies` trait, so your models behave exactly as they would in production — without an HTTP call in sight.

A few assertions are available on the fake:

```php
CurrencyConverter::assertConverted('USD', 'SEK');                 // a USD -> SEK conversion happened
CurrencyConverter::assertConverted();                            // any conversion happened
CurrencyConverter::assertConverted(fn ($value, $from, $to) => $value === 100.0);
CurrencyConverter::assertConvertedTimes(2, 'USD', 'SEK');
CurrencyConverter::assertNothingConverted();
```

Prefer to exercise the real conversion path? Reach for the `fixed` driver (or `Http::fake()` with the HTTP providers) to stay fast and offline:

```php
config()->set('currency-converter.default', 'fixed');
config()->set('currency-converter.drivers.fixed.rates', ['USD' => ['SEK' => 10.0]]);

expect(CurrencyConverter::convert(100, 'USD', 'SEK'))->toBe(1000.0);
```

You can run the package's own test suite with:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Erik Bernskiöld](https://bernskiold.com)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.
