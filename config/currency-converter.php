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
