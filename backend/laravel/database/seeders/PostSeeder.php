<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Carbon;

class PostSeeder extends Seeder
{
    public function run()
    {
        // 管理者ユーザーを取得
        $adminUser = AdminUser::first();
        
        if (!$adminUser) {
            echo "管理者ユーザーが存在しません。先にAdminUserSeederを実行してください。\n";
            return;
        }

        // カテゴリとタグを取得
        $categoryIds = Category::pluck('id')->toArray();
        $tagIds = Tag::pluck('id')->toArray();

        // 10件の投稿を作成
        for ($i = 1; $i <= 10; $i++) {
            $post = Post::create([
                'admin_user_id' => $adminUser->id,
                'author' => $adminUser->name,
                'title' => 'テスト投稿' . $i,
                'body' => 'テスト投稿' . $i . 'の本文テキストです',
                'published_at' => Carbon::now()->subDays(10 - $i)->setTime(12, 0, 0),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // ランダムにカテゴリを紐付け（1〜3個）
            if (!empty($categoryIds)) {
                $randomCategories = array_rand(array_flip($categoryIds), rand(1, min(3, count($categoryIds))));
                $post->categories()->sync(is_array($randomCategories) ? $randomCategories : [$randomCategories]);
            }

            // ランダムにタグを紐付け（1〜3個）
            if (!empty($tagIds)) {
                $randomTags = array_rand(array_flip($tagIds), rand(1, min(3, count($tagIds))));
                $post->tags()->sync(is_array($randomTags) ? $randomTags : [$randomTags]);
            }
        }
    }
}
