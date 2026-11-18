<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function store(StorePaymentRequest $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Pesanan tidak ditemukan', [], 404);
        }

        if ($order->payment()->exists()) {
            return $this->error('Pembayaran untuk pesanan ini sudah ada', ['order_id' => 'duplikat'], 422);
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $request->validated('payment_method'),
            'payment_status' => 'pending',
            'transaction_reference' => 'TRX-'.$order->order_number,
        ]);

        return $this->success('Pembayaran berhasil dibuat', new PaymentResource($payment), 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if (! $request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return $this->error('Pesanan tidak ditemukan', [], 404);
        }

        $payment = $order->payment;

        if (! $payment) {
            return $this->error('Pembayaran tidak ditemukan', [], 404);
        }

        return $this->success('Detail pembayaran berhasil diambil', new PaymentResource($payment));
    }

    public function markPaid(Request $request, Payment $payment): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Akses hanya untuk admin');
        }

        $payment->update(['payment_status' => 'paid', 'paid_at' => now()]);

        return $this->success('Pembayaran ditandai lunas', new PaymentResource($payment->fresh()));
    }
}
