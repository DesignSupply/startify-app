<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return view('pages.admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return view('pages.admin.categories.create');
    }

    public function store(CategoryStoreRequest $request)
    {
        $validated = $request->validated();

        $category = new Category();
        $category->name = $validated['name'];
        $category->slug = $validated['slug'];
        $category->save();

        return redirect()->route('categories.index')
            ->with('status', 'カテゴリを作成しました。');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);

        return view('pages.admin.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(CategoryUpdateRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validated();

        $category->name = $validated['name'];
        $category->slug = $validated['slug'];
        $category->save();

        return redirect()->route('categories.index')
            ->with('status', 'カテゴリを更新しました。');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if (!$category->is_deleted) {
            $category->is_deleted = true;
            $category->deleted_at = now();
            $category->save();
        }

        return redirect()->route('categories.index')
            ->with('status', 'カテゴリを削除しました。');
    }

    public function restore($id)
    {
        $category = Category::findOrFail($id);

        if ($category->is_deleted) {
            $category->is_deleted = false;
            $category->deleted_at = null;
            $category->save();
        }

        return redirect()->route('categories.index')
            ->with('status', 'カテゴリを復元しました。');
    }
}
