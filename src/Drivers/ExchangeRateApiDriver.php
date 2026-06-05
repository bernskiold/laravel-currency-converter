<?php

namespace Bernskiold\LaravelCurrencyConverter\Drivers;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

class ExchangeRateApiDriver implements ExchangeRateProvider
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected HttpFactory $http,
        protected array $config = [],
    ) {}

    public function getRate(string $from, string $to): float
    {
        $apiKey = $this->config['api_key'] ?? null;

        try {
            if (! empty($apiKey)) {
                $rate = $this->http
                    ->baseUrl($this->config['base_url'] ?? 'https://v6.exchangerate-api.com')
                    ->timeout((int) ($this->config['timeout'] ?? 10))
                    ->get("/v6/{$apiKey}/pair/{$from}/{$to}")
                    ->throw()
                    ->json('conversion_rate');
            } else {
                $rate = $this->http
                    ->baseUrl($this->config['open_base_url'] ?? 'https://open.er-api.com')
                    ->timeout((int) ($this->config['timeout'] ?? 10))
                    ->get("/v6/latest/{$from}")
                    ->throw()
                    ->json("rates.{$to}");
            }
        } catch (Throwable $e) {
            throw CurrencyConversionException::requestFailed('exchangerate_api', $from, $to, $e);
        }

        if (! is_numeric($rate)) {
            throw CurrencyConversionException::missingRate('exchangerate_api', $from, $to);
        }

        return (float) $rate;
    }
}
