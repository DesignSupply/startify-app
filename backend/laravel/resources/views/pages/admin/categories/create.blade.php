@extends('layouts.default')

@section('title', 'カテゴリ作成')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>カテゴリ作成</h1>

    @foreach(['name', 'slug'] as $field)
        @error($field)
            <p style="color: red;">{{ $message }}</p>
        @enderror
    @endforeach

    <form method="POST" action="{{ route('categories.store') }}">

        @csrf

        <div>
            <label for="name">名前</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}">
        </div>
        <div>
            <label for="slug">スラッグ</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug') }}">
            <small>英数字とハイフンのみ</small>
        </div>
        <div>
            <button type="submit">作成する</button>
            <a href="{{ route('categories.index') }}">戻る</a>
        </div>
    </form>
</main>
@endsection

@section('script_body')
@endsection
