<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin Kantin', 'email' => 'admin@kantin.test', 'role' => 'admin', 'phone' => '081200000001'],
            ['name' => 'Riska Dinar', 'email' => 'riska@kantin.test', 'role' => 'customer', 'phone' => '081200000002'],
            ['name' => 'Said Kemal', 'email' => 'said@kantin.test', 'role' => 'customer', 'phone' => '081200000003'],
            ['name' => 'Arjuna Pratama', 'email' => 'arjuna@kantin.test', 'role' => 'customer', 'phone' => '081200000004'],
            ['name' => 'Dahrul Ambia', 'email' => 'dahrul@kantin.test', 'role' => 'customer', 'phone' => '081200000005'],
            ['name' => 'Kevin Alvareza', 'email' => 'kevin@kantin.test', 'role' => 'admin', 'phone' => '081200000006'],
        ];

        foreach ($users as $row) {
            User::updateOrCreate(
                ['email' => $row['email']],
                $row + ['password' => 'password123']
            );
        }
    }
}
