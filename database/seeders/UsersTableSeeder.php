<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(10)->create();
        
        // Or 
        User::factory()->create([
            'name' => 'Moufed',
            'email' => 'moffeddebo@gmail.com',
            'password' => Hash::make('password'),
        ]);
    }
}
