<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'payment_id' => $this->id,
            'order_id' => $this->order_id,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'transaction_reference' => $this->transaction_reference,
            'paid_at' => $this->paid_at?->toIso8601String(),
        ];
    }
}
