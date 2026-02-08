<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Http\Requests\TagStoreRequest;
use App\Http\Requests\TagUpdateRequest;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::orderBy('name')->get();

        return view('pages.admin.tags.index', [
            'tags' => $tags,
        ]);
    }

    public function create()
    {
        return view('pages.admin.tags.create');
    }

    public function store(TagStoreRequest $request)
    {
        $validated = $request->validated();

        $tag = new Tag();
        $tag->name = $validated['name'];
        $tag->slug = $validated['slug'];
        $tag->save();

        return redirect()->route('tags.index')
            ->with('status', 'タグを作成しました。');
    }

    public function edit($id)
    {
        $tag = Tag::findOrFail($id);

        return view('pages.admin.tags.edit', [
            'tag' => $tag,
        ]);
    }

    public function update(TagUpdateRequest $request, $id)
    {
        $tag = Tag::findOrFail($id);
        $validated = $request->validated();

        $tag->name = $validated['name'];
        $tag->slug = $validated['slug'];
        $tag->save();

        return redirect()->route('tags.index')
            ->with('status', 'タグを更新しました。');
    }

    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);

        if (!$tag->is_deleted) {
            $tag->is_deleted = true;
            $tag->deleted_at = now();
            $tag->save();
        }

        return redirect()->route('tags.index')
            ->with('status', 'タグを削除しました。');
    }

    public function restore($id)
    {
        $tag = Tag::findOrFail($id);

        if ($tag->is_deleted) {
            $tag->is_deleted = false;
            $tag->deleted_at = null;
            $tag->save();
        }

        return redirect()->route('tags.index')
            ->with('status', 'タグを復元しました。');
    }
}
