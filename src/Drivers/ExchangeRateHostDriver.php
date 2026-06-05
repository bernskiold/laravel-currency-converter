<?php

namespace Bernskiold\LaravelCurrencyConverter\Drivers;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

class ExchangeRateHostDriver implements ExchangeRateProvider
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
            throw CurrencyConversionException::missingApiKey('exchangerate_host');
        }

        try {
            $response = $this->http
                ->baseUrl($this->config['base_url'] ?? 'https://api.exchangerate.host')
                ->timeout((int) ($this->config['timeout'] ?? 10))
                ->get('/convert', [
                    'access_key' => $this->config['access_key'],
                    'from' => $from,
                    'to' => $to,
                    'amount' => 1,
                ])
                ->throw();
        } catch (Throwable $e) {
            throw CurrencyConversionException::requestFailed('exchangerate_host', $from, $to, $e);
        }

        $rate = $response->json('result');

        if (! is_numeric($rate)) {
            throw CurrencyConversionException::missingRate('exchangerate_host', $from, $to);
        }

        return (float) $rate;
    }
}
