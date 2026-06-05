<?php

namespace Bernskiold\LaravelCurrencyConverter\Drivers;

use Bernskiold\LaravelCurrencyConverter\Contracts\ExchangeRateProvider;
use Bernskiold\LaravelCurrencyConverter\Exceptions\CurrencyConversionException;
use Illuminate\Database\ConnectionResolverInterface;
use Throwable;

class DatabaseDriver implements ExchangeRateProvider
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected ConnectionResolverInterface $db,
        protected array $config = [],
    ) {}

    public function getRate(string $from, string $to): float
    {
        $columns = $this->config['columns'] ?? [];
        $fromColumn = $columns['from'] ?? 'from_currency';
        $toColumn = $columns['to'] ?? 'to_currency';
        $rateColumn = $columns['rate'] ?? 'rate';

        try {
            $rate = $this->db->connection($this->config['connection'] ?? null)
                ->table($this->config['table'] ?? 'exchange_rates')
                ->where($fromColumn, $from)
                ->where($toColumn, $to)
                ->value($rateColumn);
        } catch (Throwable $e) {
            throw CurrencyConversionException::requestFailed('database', $from, $to, $e);
        }

        if (! is_numeric($rate)) {
            throw CurrencyConversionException::missingRate('database', $from, $to);
        }

        return (float) $rate;
    }
}
