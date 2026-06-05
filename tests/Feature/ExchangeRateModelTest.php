<?php

use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;
use Bernskiold\LaravelCurrencyConverter\Models\ExchangeRate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Cache::flush();

    Schema::create('exchange_rates', function (Blueprint $table) {
        $table->id();
        $table->string('from_currency', 3);
        $table->string('to_currency', 3);
        $table->decimal('rate', 20, 10);
        $table->timestamps();
        $table->unique(['from_currency', 'to_currency']);
    });
});

it('stores and updates a rate with setRate', function () {
    ExchangeRate::setRate('usd', 'sek', 10.0);

    expect(ExchangeRate::forPair('USD', 'SEK')->value('rate'))->toEqual(10.0);

    ExchangeRate::setRate('USD', 'SEK', 11.0);

    expect(ExchangeRate::count())->toBe(1)
        ->and(ExchangeRate::forPair('USD', 'SEK')->value('rate'))->toEqual(11.0);
});

it('reads its table name from config', function () {
    expect((new ExchangeRate)->getTable())->toBe('exchange_rates');

    config()->set('currency-converter.drivers.database.table', 'fx_rates');

    expect((new ExchangeRate)->getTable())->toBe('fx_rates');
});

it('feeds the database driver', function () {
    config()->set('currency-converter.default', 'database');

    ExchangeRate::setRate('USD', 'SEK', 9.5);

    expect(CurrencyConverter::convert(100, 'USD', 'SEK'))->toBe(950.0);
});
