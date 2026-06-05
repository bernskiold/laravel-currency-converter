<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | The exchange rate provider used by default. Each driver is configured in
    | the "drivers" array below. You may register your own drivers at runtime
    | with CurrencyConverter::extend().
    |
    | Supported out of the box: "frankfurter", "exchangerate_host", "fixed".
    |
    */
    'default' => env('CURRENCY_CONVERTER_DRIVER', 'frankfurter'),

    /*
    |--------------------------------------------------------------------------
    | Base Currency
    |--------------------------------------------------------------------------
    |
    | The currency that amounts are normalised to when using the toBase()
    | helper. This is a convenience for applications that store a single
    | reporting currency.
    |
    */
    'base_currency' => env('CURRENCY_CONVERTER_BASE', 'SEK'),

    /*
    |--------------------------------------------------------------------------
    | Decimals
    |--------------------------------------------------------------------------
    |
    | The number of decimals converted amounts are rounded to.
    |
    */
    'decimals' => 2,

    /*
    |--------------------------------------------------------------------------
    | Number Formatting
    |--------------------------------------------------------------------------
    |
    | How amounts are formatted for display by the format() helper and the
    | HandlesCurrencyConversion trait. Defaults to US conventions
    | (e.g. "1,234.56"). For Swedish formatting use ',' and ' '.
    |
    */
    'formatting' => [
        'decimals' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Exchange rates are cached to avoid hitting the provider on every
    | conversion. Set "ttl" to 0 (or null) to disable caching entirely.
    | "store" may be any configured cache store, or null for the default.
    |
    */
    'cache' => [
        'store' => env('CURRENCY_CONVERTER_CACHE_STORE'),
        'ttl' => env('CURRENCY_CONVERTER_CACHE_TTL', 86400),
        'prefix' => 'currency_converter',
    ],

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    */
    'drivers' => [

        /*
         | Frankfurter (https://frankfurter.dev) — free, keyless, backed by
         | European Central Bank reference (mid-market) rates, updated daily.
         */
        'frankfurter' => [
            'base_url' => env('FRANKFURTER_URL', 'https://api.frankfurter.dev'),
            'timeout' => 10,
        ],

        /*
         | exchangerate.host — requires an access key.
         */
        'exchangerate_host' => [
            'base_url' => env('EXCHANGERATE_HOST_URL', 'https://api.exchangerate.host'),
            'access_key' => env('EXCHANGERATE_HOST_KEY'),
            'timeout' => 10,
        ],

        /*
         | ExchangeRate-API (https://www.exchangerate-api.com) — broad currency
         | coverage. With an API key the authenticated v6 endpoint is used; without
         | one it falls back to the free, keyless open.er-api.com endpoint.
         */
        'exchangerate_api' => [
            'base_url' => env('EXCHANGERATE_API_URL', 'https://v6.exchangerate-api.com'),
            'open_base_url' => env('EXCHANGERATE_API_OPEN_URL', 'https://open.er-api.com'),
            'api_key' => env('EXCHANGERATE_API_KEY'),
            'timeout' => 10,
        ],

        /*
         | Open Exchange Rates (https://openexchangerates.org) — requires an app ID.
         | Note: the free plan only supports a USD base currency.
         */
        'open_exchange_rates' => [
            'base_url' => env('OPEN_EXCHANGE_RATES_URL', 'https://openexchangerates.org'),
            'app_id' => env('OPEN_EXCHANGE_RATES_APP_ID'),
            'timeout' => 10,
        ],

        /*
         | Fixer (https://fixer.io) — requires an access key.
         | Note: the free plan only supports a EUR base currency.
         */
        'fixer' => [
            'base_url' => env('FIXER_URL', 'https://data.fixer.io'),
            'access_key' => env('FIXER_ACCESS_KEY'),
            'timeout' => 10,
        ],

        /*
         | Database — read rates from a table you manage yourself. Ideal when you
         | need controlled, auditable rates rather than live market data. Publish
         | the migration with:
         |
         |   php artisan vendor:publish --tag=currency-converter-migrations
         */
        'database' => [
            'connection' => env('CURRENCY_CONVERTER_DB_CONNECTION'),
            'table' => 'exchange_rates',
            'columns' => [
                'from' => 'from_currency',
                'to' => 'to_currency',
                'rate' => 'rate',
            ],
        ],

        /*
         | Fixed — static rates defined in configuration. Useful for testing,
         | offline environments, or pinning a known rate. Keyed by base
         | currency, then quote currency.
         |
         | 'rates' => ['USD' => ['SEK' => 10.5], 'EUR' => ['SEK' => 11.2]],
         */
        'fixed' => [
            'rates' => [],
        ],

    ],

];
