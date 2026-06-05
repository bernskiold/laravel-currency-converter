<?php

use Bernskiold\LaravelCurrencyConverter\Drivers\DatabaseDriver;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('exchange_rates', function (Blueprint $table) {
        $table->id();
        $table->string('from_currency', 3);
        $table->string('to_currency', 3);
        $table->decimal('rate', 20, 10);
        $table->timestamps();
    });
});

function databaseDriver(): DatabaseDriver
{
    return new DatabaseDriver(app(ConnectionResolverInterface::class), [
        'table' => 'exchange_rates',
    ]);
}

it('reads a rate from the database', function () {
    DB::table('exchange_rates')->insert([
        'from_currency' => 'USD',
        'to_currency' => 'SEK',
        'rate' => 10.123,
    ]);

    expect(databaseDriver()->getRate('USD', 'SEK'))->toBe(10.123);
});

it('throws when no rate row exists for the pair', function () {
    databaseDriver()->getRate('EUR', 'SEK');
})->throws(CurrencyConversionException::class);

it('honours custom column names', function () {
    Schema::create('fx', function (Blueprint $table) {
        $table->string('src', 3);
        $table->string('dst', 3);
        $table->decimal('value', 20, 10);
    });

    DB::table('fx')->insert(['src' => 'GBP', 'dst' => 'SEK', 'value' => 13.5]);

    $driver = new DatabaseDriver(app(ConnectionResolverInterface::class), [
        'table' => 'fx',
        'columns' => ['from' => 'src', 'to' => 'dst', 'rate' => 'value'],
    ]);

    expect($driver->getRate('GBP', 'SEK'))->toBe(13.5);
});
