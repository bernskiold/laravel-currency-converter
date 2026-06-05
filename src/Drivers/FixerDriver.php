<?php

namespace Bernskiold\LaravelCurrencyConverter\Drivers;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

class FixerDriver implements ExchangeRateProvider
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
        if (empty($this->config['access_key'])) {
            throw CurrencyConversionException::missingApiKey('fixer');
        }

        try {
            $response = $this->http
                ->baseUrl($this->config['base_url'] ?? 'https://data.fixer.io')
                ->timeout((int) ($this->config['timeout'] ?? 10))
                ->get('/api/latest', [
                    'access_key' => $this->config['access_key'],
                    'base' => $from,
                    'symbols' => $to,
                ])
                ->throw();
        } catch (Throwable $e) {
            throw CurrencyConversionException::requestFailed('fixer', $from, $to, $e);
        }

        if ($response->json('success') !== true) {
            throw CurrencyConversionException::requestFailed('fixer', $from, $to);
        }

        $rate = $response->json("rates.{$to}");

        if (! is_numeric($rate)) {
            throw CurrencyConversionException::missingRate('fixer', $from, $to);
        }

        return (float) $rate;
    }
}
