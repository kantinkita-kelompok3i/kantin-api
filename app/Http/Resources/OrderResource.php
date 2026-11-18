<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'order_code' => $this->order_number,
            'order_status' => $this->status,
            'total_amount' => (float) $this->total_amount,
            'notes' => $this->notes,
            'pickup_time' => $this->pickup_time?->toIso8601String(),
            'ordered_at' => $this->ordered_at?->toIso8601String(),
            'customer' => new UserResource($this->whenLoaded('user')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
        ];
    }
}
