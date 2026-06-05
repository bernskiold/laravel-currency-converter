<?php

namespace Bernskiold\LaravelCurrencyConverter\Tests\Models;

use Bernskiold\LaravelCurrencyConverter\Concerns\ConvertsCurrencies;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use ConvertsCurrencies;

    protected $guarded = [];

    protected static array $currencyConversions = [
        'amount' => 'amount_sek',
    ];

    protected $casts = [
        'amount' => 'float',
        'amount_sek' => 'float',
    ];
}
