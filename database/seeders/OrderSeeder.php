<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $products = Product::available()->get();
        $statuses = ['pending', 'processing', 'ready', 'completed', 'cancelled'];
        $methods = ['cash', 'qris', 'transfer'];

        foreach ($customers as $i => $customer) {
            for ($n = 0; $n < 2; $n++) {
                $number = sprintf('KC-%s-%03d', now()->format('Ymd'), $i * 2 + $n + 1);

                $order = Order::updateOrCreate(
                    ['order_number' => $number],
                    [
                        'user_id' => $customer->id,
                        'status' => $statuses[($i + $n) % count($statuses)],
                        'total_amount' => 0,
                        'notes' => $n ? 'Tanpa sambal, ya.' : null,
                        'pickup_time' => now()->addHours(1)->startOfHour(),
                        'ordered_at' => now()->subHours($i + 1),
                    ]
                );

                $items = $products->slice(($i + $n) % 5, 2);
                $total = 0;

                foreach ($items as $product) {
                    $qty = 1 + ($n % 2);
                    $subtotal = $product->price * $qty;
                    $total += $subtotal;

                    OrderItem::updateOrCreate(
                        ['order_id' => $order->id, 'product_id' => $product->id],
                        [
                            'product_name' => $product->name,
                            'unit_price' => $product->price,
                            'quantity' => $qty,
                            'subtotal' => $subtotal,
                        ]
                    );
                }

                $order->update(['total_amount' => $total]);

                OrderStatusHistory::updateOrCreate(
                    ['order_id' => $order->id, 'status' => 'pending'],
                    ['note' => 'Pesanan dibuat', 'changed_by' => $customer->id]
                );

                if ($order->status !== 'pending') {
                    OrderStatusHistory::updateOrCreate(
                        ['order_id' => $order->id, 'status' => $order->status],
                        ['note' => 'Status diperbarui', 'changed_by' => User::where('role', 'admin')->first()->id]
                    );
                }

                Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'payment_method' => $methods[($i + $n) % count($methods)],
                        'payment_status' => in_array($order->status, ['completed', 'ready']) ? 'paid' : 'pending',
                        'transaction_reference' => 'TRX-'.$number,
                        'paid_at' => in_array($order->status, ['completed', 'ready']) ? now()->subMinutes(30) : null,
                    ]
                );
            }
        }
    }
}
