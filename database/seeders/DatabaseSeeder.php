<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
            OrderSeeder::class,
        ]);

        // ponytail: seeder hanya mengisi tabel utama; cart dibuat saat user memakai fitur cart
        assert(Product::count() >= 5 && User::count() >= 5 && Order::count() >= 5 && Payment::count() >= 5);
    }
}
