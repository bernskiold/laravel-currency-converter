<?php

namespace Bernskiold\LaravelCurrencyConverter\Tests;

use Bernskiold\LaravelCurrencyConverter\CurrencyConverterServiceProvider;
use Bernskiold\LaravelCurrencyConverter\Facades\CurrencyConverter;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
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
}
