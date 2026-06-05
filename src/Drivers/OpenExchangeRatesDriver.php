<?php

namespace Bernskiold\LaravelCurrencyConverter\Drivers;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Throwable;

class OpenExchangeRatesDriver implements ExchangeRateProvider
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
        if (empty($this->config['app_id'])) {
            throw CurrencyConversionException::missingApiKey('open_exchange_rates');
        }

        try {
            $rate = $this->http
                ->baseUrl($this->config['base_url'] ?? 'https://openexchangerates.org')
                ->timeout((int) ($this->config['timeout'] ?? 10))
                ->get('/api/latest.json', [
                    'app_id' => $this->config['app_id'],
                    'base' => $from,
                    'symbols' => $to,
                ])
                ->throw()
                ->json("rates.{$to}");
        } catch (Throwable $e) {
            throw CurrencyConversionException::requestFailed('open_exchange_rates', $from, $to, $e);
        }

        if (! is_numeric($rate)) {
            throw CurrencyConversionException::missingRate('open_exchange_rates', $from, $to);
        }

        return (float) $rate;
    }
}
