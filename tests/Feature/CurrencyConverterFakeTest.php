<?php

use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;
use Bernskiold\LaravelCurrencyConverter\Tests\Models\Order;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\AssertionFailedError;

it('converts 1:1 by default and never hits the network', function () {
    Http::fake();
    CurrencyConverter::fake();

    expect(CurrencyConverter::convert(100, 'USD', 'SEK'))->toBe(100.0);

    Http::assertNothingSent();
});

it('uses the rates provided to the fake', function () {
    CurrencyConverter::fake(['USD' => ['SEK' => 10.0]]);

    expect(CurrencyConverter::convert(100, 'USD', 'SEK'))->toBe(1000.0)
        ->and(CurrencyConverter::rate('USD', 'SEK'))->toBe(10.0)
        ->and(CurrencyConverter::toBase(100, 'USD'))->toBe(1000.0);
});

it('records conversions for assertions', function () {
    CurrencyConverter::fake(['USD' => ['SEK' => 10.0]]);

    CurrencyConverter::toBase(50, 'USD');

    CurrencyConverter::assertConverted('USD', 'SEK');
    CurrencyConverter::assertConvertedTimes(1, 'USD', 'SEK');
});

it('supports closure assertions', function () {
    CurrencyConverter::fake(['USD' => ['SEK' => 10.0]]);

    CurrencyConverter::convert(100, 'USD', 'SEK');

    CurrencyConverter::assertConverted(fn (float $value, string $from, string $to) => $value === 100.0 && $to === 'SEK');
});

it('asserts nothing was converted', function () {
    CurrencyConverter::fake();

    CurrencyConverter::assertNothingConverted();
});

it('fails when an expected conversion did not happen', function () {
    CurrencyConverter::fake();

    CurrencyConverter::assertConverted('GBP', 'SEK');
})->throws(AssertionFailedError::class);

it('drives model conversion through the fake', function () {
    CurrencyConverter::fake(['USD' => ['SEK' => 12.0]]);

    $order = Order::create(['currency' => 'USD', 'amount' => 100]);

    expect($order->amount_sek)->toBe(1200.0);

    CurrencyConverter::assertConverted('USD', 'SEK');
});
