<?php

namespace Bernskiold\LaravelCurrencyConverter\Drivers;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

class FrankfurterDriver implements ExchangeRateProvider
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
        try {
            $response = $this->http
                ->baseUrl($this->config['base_url'] ?? 'https://api.frankfurter.dev')
                ->timeout((int) ($this->config['timeout'] ?? 10))
                ->get('/v1/latest', [
                    'base' => $from,
                    'symbols' => $to,
                ])
                ->throw();
        } catch (Throwable $e) {
            throw CurrencyConversionException::requestFailed('frankfurter', $from, $to, $e);
        }

        $rate = $response->json("rates.{$to}");

        if (! is_numeric($rate)) {
            throw CurrencyConversionException::missingRate('frankfurter', $from, $to);
        }

        return (float) $rate;
    }
}
