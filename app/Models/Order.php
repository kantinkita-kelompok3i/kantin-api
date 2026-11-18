<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = ['pending', 'processing', 'ready', 'completed', 'cancelled'];

    protected $fillable = [
        'user_id', 'order_number', 'status', 'total_amount',
        'notes', 'pickup_time', 'ordered_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'pickup_time' => 'datetime',
            'ordered_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }
}
