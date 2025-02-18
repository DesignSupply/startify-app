<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        AdminUser::create([
            'name' => '管理者 太郎',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
