<?php

namespace Bernskiold\LaravelCurrencyConverter;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;

class CurrencyConverterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/currency-converter.php', 'currency-converter'
        );

        $this->app->singleton(CurrencyConverterManager::class, fn (Application $app) => new CurrencyConverterManager($app));

        $this->app->singleton(CurrencyConverter::class, fn (Application $app) => new CurrencyConverter(
            $app->make(CurrencyConverterManager::class),
            $app->make('cache'),
            $app->make('config'),
        ));
    }

    public function boot(): void
    {
        if (class_exists(AboutCommand::class)) {
            AboutCommand::add('Laravel Currency Converter', fn () => [
                'Default Driver' => config('currency-converter.default'),
                'Base Currency' => config('currency-converter.base_currency'),
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/currency-converter.php' => config_path('currency-converter.php'),
        ], 'currency-converter-config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_exchange_rates_table.php.stub' => $this->app->databasePath(
                'migrations/'.date('Y_m_d_His').'_create_exchange_rates_table.php'
            ),
        ], 'currency-converter-migrations');
    }
}
