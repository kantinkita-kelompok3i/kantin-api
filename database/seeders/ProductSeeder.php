<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $menu = [
            'Makanan Berat' => [
                ['Nasi Goreng Spesial Kampus', 15000, 20],
                ['Mie Ayam Bakso', 13000, 25],
                ['Ayam Geprek Sambal Bawang', 16000, 18],
                ['Nasi Uduk Telur Balado', 12000, 22],
                ['Soto Ayam Kampung', 14000, 15],
            ],
            'Makanan Ringan' => [
                ['Tahu Crispy', 8000, 30],
                ['Pisang Goreng Keju', 10000, 24],
                ['Roti Bakar Coklat', 9000, 20],
                ['Kentang Goreng', 12000, 26],
                ['Cireng Bumbu Rujak', 8000, 28],
            ],
            'Minuman Dingin' => [
                ['Es Teh Manis Jumbo', 5000, 50],
                ['Jus Alpukat', 12000, 18],
                ['Es Jeruk Peras', 7000, 35],
                ['Thai Tea', 10000, 22],
                ['Air Mineral Dingin', 4000, 60],
            ],
            'Minuman Hangat' => [
                ['Kopi Hitam', 6000, 40],
                ['Teh Tarik', 9000, 25],
                ['Wedang Jahe', 7000, 20],
                ['Kopi Susu Gula Aren', 12000, 30],
                ['Coklat Panas', 11000, 18],
            ],
            'Paket Hemat' => [
                ['Paket Nasi + Es Teh', 18000, 25],
                ['Paket Mie + Jeruk', 17000, 20],
                ['Paket Ayam + Air Mineral', 19000, 15],
                ['Paket Camilan + Kopi', 15000, 22],
                ['Paket Sarapan Roti + Susu', 16000, 18],
            ],
            'Dessert' => [
                ['Puding Coklat', 8000, 16],
                ['Es Krim Vanilla', 10000, 20],
                ['Brownies Kukus', 12000, 14],
                ['Es Campur', 11000, 18],
                ['Pancake Madu', 13000, 12],
            ],
        ];

        foreach ($menu as $categoryName => $products) {
            $category = Category::where('name', $categoryName)->firstOrFail();

            foreach ($products as [$name, $price, $stock]) {
                Product::updateOrCreate(
                    ['name' => $name],
                    [
                        'category_id' => $category->id,
                        'description' => $name.' siap disajikan di kantin kampus.',
                        'price' => $price,
                        'image_url' => '/images/'.str()->slug($name).'.jpg',
                        'stock' => $stock,
                        'is_available' => true,
                    ]
                );
            }
        }
    }
}
