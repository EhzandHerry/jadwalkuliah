<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('iniadmin123'),
            'phone' => '08123456789',
            'unique_number' => 0,
            'is_admin' => true,
        ]);

        User::updateOrCreate([
            'name' => 'Nurwahyu Alamsyah',
            'email' => 'nurwahyu.alamsyah@umy.ac.id',
            'password' => Hash::make('12345678'),
            'phone' => '08123129876',
            'unique_number' => '1234567890',
            'is_admin' => false,
        ]);

        User::updateOrCreate([
            'name' => 'Reza Giga Isnanda',
            'email' => 'reza.gigaisnanda@ft.umy.ac.id',
            'password' => Hash::make('12345678'),
            'phone' => '08987129098',
            'unique_number' => '0503068601',
            'is_admin' => false,
        ]);

        User::updateOrCreate([
            'name' => 'Slamet Riyadi',
            'email' => 'riyadi@umy.ac.id',
            'password' => Hash::make('12345678'),
            'phone' => '08123129123',
            'unique_number' => '0509087801',
            'is_admin' => false,
        ]);
    }
}
