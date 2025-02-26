<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::insert([
            [
                'name' => 'テスト 太郎',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'テスト 花子',
                'email' => 'sample@example.com',
                'password' => Hash::make('qwerty'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
