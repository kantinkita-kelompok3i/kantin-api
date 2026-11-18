<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function summary(): JsonResponse
    {
        return $this->success('Ringkasan dashboard berhasil diambil', [
            'total_products' => Product::count(),
            'available_products' => Product::available()->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_orders' => Order::count(),
            'orders_by_status' => Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'revenue' => (float) Order::where('status', 'completed')->sum('total_amount'),
        ]);
    }
}
