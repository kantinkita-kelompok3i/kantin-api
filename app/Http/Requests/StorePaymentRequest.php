<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::in(Payment::METHODS)],
        ];
    }

    public function messages(): array
    {
        return ['payment_method.in' => 'Metode pembayaran tidak tersedia'];
    }
}
