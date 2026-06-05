<?php

namespace Bernskiold\LaravelCurrencyConverter\Tests;

use Bernskiold\LaravelCurrencyConverter\CurrencyConverterServiceProvider;
use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('currency')->nullable();
            $table->decimal('amount', 16, 2)->nullable();
            $table->decimal('amount_sek', 16, 2)->nullable();
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            CurrencyConverterServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'CurrencyConverter' => CurrencyConverter::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
