<?php

namespace Bernskiold\LaravelCurrencyConverter\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $from_currency
 * @property string $to_currency
 * @property float $rate
 */
class ExchangeRate extends Model
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'float',
        ];
    }

    public function getTable(): string
    {
        return $this->table ?? (string) config('currency-converter.drivers.database.table', 'exchange_rates');
    }

    public function getConnectionName(): ?string
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $connection = config('currency-converter.drivers.database.connection');

        return is_string($connection) ? $connection : null;
    }

    /**
     * Create or update the rate for a currency pair.
     */
    public static function setRate(string $from, string $to, float $rate): static
    {
        return static::query()->updateOrCreate(
            [
                static::columnName('from') => strtoupper($from),
                static::columnName('to') => strtoupper($to),
            ],
            [
                static::columnName('rate') => $rate,
            ],
        );
    }

    /**
     * @param  Builder<ExchangeRate>  $query
     * @return Builder<ExchangeRate>
     */
    public function scopeForPair(Builder $query, string $from, string $to): Builder
    {
        return $query
            ->where(static::columnName('from'), strtoupper($from))
            ->where(static::columnName('to'), strtoupper($to));
    }

    protected static function columnName(string $key): string
    {
        $columns = (array) config('currency-converter.drivers.database.columns', []);

        $defaults = [
            'from' => 'from_currency',
            'to' => 'to_currency',
            'rate' => 'rate',
        ];

        return (string) ($columns[$key] ?? $defaults[$key]);
    }
}
