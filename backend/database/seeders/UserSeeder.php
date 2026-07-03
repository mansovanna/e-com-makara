<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@1234gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);


        User::create([
            'name' => 'Kitchen Staff',
            'email' => 'kitchen@1234gmail.com',
            'password' => Hash::make('password'),
            'role' => 'kitchen',
        ]);

        User::create([
            'name' => 'Cashier',
            'email' => 'cashier@1234gmail.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);
    }
}
