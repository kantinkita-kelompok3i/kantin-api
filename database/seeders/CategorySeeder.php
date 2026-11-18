<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Makanan Berat', 'description' => 'Nasi, mie, ayam dan lauk utama'],
            ['name' => 'Makanan Ringan', 'description' => 'Gorengan, roti dan camilan'],
            ['name' => 'Minuman Dingin', 'description' => 'Es teh, jus dan minuman kemasan'],
            ['name' => 'Minuman Hangat', 'description' => 'Kopi, teh dan wedang'],
            ['name' => 'Paket Hemat', 'description' => 'Kombinasi makanan dan minuman'],
            ['name' => 'Dessert', 'description' => 'Puding, es krim dan kue'],
        ];

        foreach ($data as $row) {
            Category::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
