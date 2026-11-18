<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public const METHODS = ['cash', 'qris', 'transfer'];

    protected $fillable = [
        'order_id', 'payment_method', 'payment_status',
        'transaction_reference', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
