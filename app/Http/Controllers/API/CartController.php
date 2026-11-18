<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        return $this->success('Keranjang berhasil diambil', $this->present($this->cartFor($request)));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return $this->error('Data keranjang tidak valid', $validator->errors()->toArray(), 422);
        }

        $cart = $this->cartFor($request);
        $item = $cart->items()->firstOrNew(['product_id' => $request->integer('product_id')]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $request->integer('quantity');
        $item->save();

        return $this->success('Item masuk keranjang', $this->present($cart->fresh()), 201);
    }

    public function update(Request $request, int $item): JsonResponse
    {
        $cartItem = $this->cartFor($request)->items()->find($item);

        if (! $cartItem) {
            return $this->error('Item keranjang tidak ditemukan', [], 404);
        }

        $validator = Validator::make($request->all(), ['quantity' => ['required', 'integer', 'min:1']]);

        if ($validator->fails()) {
            return $this->error('Jumlah tidak valid', $validator->errors()->toArray(), 422);
        }

        if ($cartItem->product->stock < $request->integer('quantity')) {
            return $this->error('Stok tidak mencukupi', ['quantity' => 'Stok tersisa '.$cartItem->product->stock], 422);
        }

        $cartItem->update(['quantity' => $request->integer('quantity')]);

        return $this->success('Keranjang berhasil diperbarui', $this->present($cartItem->cart));
    }

    public function destroy(Request $request, int $item): JsonResponse
    {
        $cartItem = $this->cartFor($request)->items()->find($item);

        if (! $cartItem) {
            return $this->error('Item keranjang tidak ditemukan', [], 404);
        }

        $cart = $cartItem->cart;
        $cartItem->delete();

        return $this->success('Item dihapus dari keranjang', $this->present($cart->fresh()));
    }

    private function cartFor(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id]);
    }

    private function present(Cart $cart): array
    {
        $cart->load('items.product');

        $items = $cart->items->map(fn (CartItem $item) => [
            'cart_item_id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name,
            'price' => (float) ($item->product?->price ?? 0),
            'quantity' => $item->quantity,
            'subtotal' => (float) ($item->product?->price ?? 0) * $item->quantity,
        ]);

        return [
            'cart_id' => $cart->id,
            'items' => $items,
            'total_items' => $items->sum('quantity'),
            'total_amount' => (float) $items->sum('subtotal'),
        ];
    }
}
