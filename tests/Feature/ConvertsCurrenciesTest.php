<?php

use Bernskiold\LaravelCurrencyConverter\Tests\Models\Order;

beforeEach(function () {
    config()->set('currency-converter.default', 'fixed');
    config()->set('currency-converter.base_currency', 'SEK');
    config()->set('currency-converter.drivers.fixed.rates', ['USD' => ['SEK' => 10.0]]);
});

it('populates the base currency amount when creating a non-base-currency record', function () {
    $order = Order::create(['currency' => 'USD', 'amount' => 100]);

    expect($order->amount_sek)->toBe(1000.0);
});

it('copies the amount directly for base currency records', function () {
    $order = Order::create(['currency' => 'SEK', 'amount' => 500]);

    expect($order->amount_sek)->toBe(500.0);
});

it('recomputes when the amount changes', function () {
    $order = Order::create(['currency' => 'USD', 'amount' => 100]);

    $order->update(['amount' => 250]);

    expect($order->amount_sek)->toBe(2500.0);
});

it('still saves the record when conversion fails', function () {
    config()->set('currency-converter.drivers.fixed.rates', []);

    $order = Order::create(['currency' => 'USD', 'amount' => 100]);

    expect($order->exists)->toBeTrue()
        ->and($order->fresh()->amount_sek)->toBeNull();
});

it('backfills a missing converted amount via recalculate', function () {
    $order = Order::create(['currency' => 'SEK', 'amount' => 100]);
    $order->forceFill(['currency' => 'USD', 'amount_sek' => null])->saveQuietly();

    $order->recalculateCurrencyConversions();

    expect($order->fresh()->amount_sek)->toBe(1000.0);
});

it('exposes the conversion map', function () {
    expect(Order::currencyConversionMap())->toBe(['amount' => 'amount_sek']);
});

it('formats an amount with its own currency using US defaults', function () {
    $order = Order::create(['currency' => 'USD', 'amount' => 1234.56]);

    expect($order->amountWithCurrency('amount'))->toBe('1,234.56 USD');
});

it('formats the converted amount in the base currency', function () {
    $order = Order::create(['currency' => 'USD', 'amount' => 100]);

    expect($order->amountInBaseCurrency('amount'))->toBe('1,000.00 SEK')
        ->and($order->amountInBaseCurrency('amount_sek'))->toBe('1,000.00 SEK');
});

it('respects configured number formatting', function () {
    config()->set('currency-converter.formatting', [
        'decimals' => 2,
        'decimal_separator' => ',',
        'thousands_separator' => ' ',
    ]);

    $order = Order::create(['currency' => 'USD', 'amount' => 1234.56]);

    expect($order->amountWithCurrency('amount'))->toBe('1 234,56 USD');
});
