<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderStatusHistoryResource;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['items'])
            ->where('user_id', $request->user()->id)
            ->latest('ordered_at')
            ->paginate(min($request->integer('limit', 10), 100));

        return $this->success('Daftar pesanan berhasil diambil', OrderResource::collection($orders), 200, [
            'page' => $orders->currentPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'last_page' => $orders->lastPage(),
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $items = collect($request->validated('items'));
        $products = Product::whereIn('id', $items->pluck('product_id'))->get()->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (! $product->is_available) {
                return $this->error('Produk tidak tersedia', ['items' => $product->name.' sedang habis'], 422);
            }

            if ($product->stock < $item['quantity']) {
                return $this->error('Stok tidak mencukupi', ['items' => $product->name.' tersisa '.$product->stock], 422);
            }
        }

        $order = DB::transaction(function () use ($request, $items, $products) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => $this->nextNumber(),
                'status' => 'pending',
                'notes' => $request->validated('notes'),
                'pickup_time' => $request->validated('pickup_time'),
                'ordered_at' => now(),
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            $order->update(['total_amount' => $total]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'note' => 'Pesanan dibuat',
                'changed_by' => $order->user_id,
            ]);

            return $order;
        });

        return $this->success('Pesanan berhasil dibuat', new OrderResource($order->load('items')), 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if (! $request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return $this->error('Pesanan tidak ditemukan', [], 404);
        }

        return $this->success('Detail pesanan berhasil diambil', new OrderResource($order->load(['items', 'payment', 'user'])));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order->update(['status' => $request->validated('status')]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $request->validated('status'),
            'note' => $request->validated('note'),
            'changed_by' => $request->user()->id,
        ]);

        return $this->success('Status pesanan berhasil diperbarui', new OrderResource($order->fresh('items')));
    }

    public function statusHistory(Request $request, Order $order): JsonResponse
    {
        if (! $request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return $this->error('Pesanan tidak ditemukan', [], 404);
        }

        return $this->success('Riwayat status berhasil diambil', OrderStatusHistoryResource::collection($order->statusHistories));
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Pesanan tidak ditemukan', [], 404);
        }

        if (! in_array($order->status, ['pending', 'cancelled'])) {
            return $this->error('Pesanan tidak bisa dibatalkan pada status ini', ['status' => $order->status], 422);
        }

        $order->update(['status' => 'cancelled']);

        return $this->success('Pesanan dibatalkan', new OrderResource($order->fresh('items')));
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $orders = Order::with(['items', 'user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('ordered_at')
            ->paginate(min($request->integer('limit', 10), 100));

        return $this->success('Semua pesanan berhasil diambil', OrderResource::collection($orders), 200, [
            'page' => $orders->currentPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'last_page' => $orders->lastPage(),
        ]);
    }

    private function nextNumber(): string
    {
        $prefix = 'KC-'.now()->format('Ymd').'-';
        $last = Order::where('order_number', 'like', $prefix.'%')->count();

        return $prefix.str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }
}
