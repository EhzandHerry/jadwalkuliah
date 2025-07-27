<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Update admin yang sudah ada atau buat baru
        $admin = User::where('email', 'admin@example.com')
                    ->orWhere('email', 'admin@gmail.com')
                    ->first();
        
        if ($admin) {
            $admin->update([
                'nama' => 'Administrator',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('iniadmin123'),
                'nidn' => 0,
                'is_admin' => true,
            ]);
        } else {
            User::create([
                'nama' => 'Administrator',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('iniadmin123'),
                'nidn' => 0,
                'is_admin' => true,
            ]);
        }
    }
}