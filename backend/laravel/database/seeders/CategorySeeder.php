<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Carbon;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $categories[] = [
                'name' => 'テストカテゴリ' . $i,
                'slug' => 'test-category-' . $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        Category::insert($categories);
    }
}
