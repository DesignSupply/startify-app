<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // テーブルのクリア
        DB::table('users')->truncate();
        DB::table('admin_users')->truncate();
        DB::table('password_reset_tokens')->truncate();
        DB::table('admin_password_reset_tokens')->truncate();

        // シーダーの実行
        $this->call([
            UserSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
