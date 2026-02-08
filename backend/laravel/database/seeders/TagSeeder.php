<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use Illuminate\Support\Carbon;

class TagSeeder extends Seeder
{
    public function run()
    {
        $tags = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $tags[] = [
                'name' => 'テストタグ' . $i,
                'slug' => 'test-tag-' . $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        Tag::insert($tags);
    }
}
