<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::orderBy('published_at', 'desc')->paginate(10);

        return view('pages.posts.index', [
            'posts' => $posts,
        ]);
    }

    public function show($id)
    {
        $post = Post::with(['categories', 'tags'])->findOrFail($id);

        return view('pages.posts.show', [
            'post' => $post,
        ]);
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $tags = Tag::active()->orderBy('name')->get();

        return view('pages.admin.posts.create', [
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public function store(PostStoreRequest $request)
    {
        $validated = $request->validated();

        $post = new Post();
        $post->admin_user_id = Auth::guard('admin')->id();
        $post->author = Auth::guard('admin')->user()->name;
        $post->title = $validated['title'];
        $post->body = $validated['body'];
        $post->published_at = $validated['published_at'];
        $post->save();

        // カテゴリ・タグの紐付け
        if (!empty($validated['categories'])) {
            $post->categories()->sync($validated['categories']);
        }
        if (!empty($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()->route('posts.index')
            ->with('status', '投稿を作成しました。');
    }

    public function edit($id)
    {
        $post = Post::with(['categories', 'tags'])->findOrFail($id);
        $categories = Category::active()->orderBy('name')->get();
        $tags = Tag::active()->orderBy('name')->get();

        return view('pages.admin.posts.edit', [
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public function update(PostUpdateRequest $request, $id)
    {
        $post = Post::findOrFail($id);
        $validated = $request->validated();

        $post->title = $validated['title'];
        $post->body = $validated['body'];
        $post->published_at = $validated['published_at'];
        $post->save();

        // カテゴリ・タグの紐付けを更新
        $post->categories()->sync($validated['categories'] ?? []);
        $post->tags()->sync($validated['tags'] ?? []);

        return redirect()->route('posts.index')
            ->with('status', '投稿を更新しました。');
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if (!$post->is_deleted) {
            $post->is_deleted = true;
            $post->deleted_at = now();
            $post->save();
        }

        return redirect()->route('posts.index')
            ->with('status', '投稿を削除しました。');
    }

    public function restore($id)
    {
        $post = Post::findOrFail($id);

        if ($post->is_deleted) {
            $post->is_deleted = false;
            $post->deleted_at = null;
            $post->save();
        }

        return redirect()->route('posts.index')
            ->with('status', '投稿を復元しました。');
    }
}
