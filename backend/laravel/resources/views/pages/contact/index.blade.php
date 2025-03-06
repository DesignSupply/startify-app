@extends('layouts.default')

@section('title', 'お問い合わせ')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>お問い合わせ</h1>

    @if(session('status'))
        <p style="color: red;">{{ session('status') }}</p>
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            <p style="color: red;">{{ $error }}</p>
        @endforeach
    @endif

    @foreach(['name', 'company', 'email', 'phone', 'url', 'inquiry_type', 'gender', 'message'] as $field)
        @error($field)
            <p style="color: red;">{{ $message }}</p>
        @enderror
    @endforeach

    <form method="POST" action="{{ route('contact.form') }}">

        @csrf

        <div>
            <label for="name">お名前</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255">
        </div>
        <div>
            <label for="company">会社名</label>
            <input type="text" id="company" name="company" value="{{ old('company') }}" maxlength="255">
        </div>
        <div>
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div>
            <label for="phone">電話番号</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" pattern="[0-9]{10,11}">
        </div>
        <div>
            <label for="url">ウェブサイトURL</label>
            <input type="url" id="url" name="url" value="{{ old('url') }}">
        </div>
        <div>
            <label>お問い合わせ種別</label>
            <div>
                <label>
                    <input type="checkbox" name="inquiry_type[]" value="種別1" {{ in_array('種別1', old('inquiry_type', [])) ? 'checked' : '' }}>
                    <span>種別1</span>
                </label>
                <label>
                    <input type="checkbox" name="inquiry_type[]" value="種別2" {{ in_array('種別2', old('inquiry_type', [])) ? 'checked' : '' }}>
                    <span>種別2</span>
                </label>
                <label>
                    <input type="checkbox" name="inquiry_type[]" value="種別3" {{ in_array('種別3', old('inquiry_type', [])) ? 'checked' : '' }}>
                    <span>種別3</span>
                </label>
            </div>
        </div>
        <div>
            <label>性別</label>
            <div>
                <label>
                    <input type="radio" name="gender" value="男性" {{ old('gender') === '男性' ? 'checked' : '' }} required>
                    <span>男性</span>
                </label>
                <label>
                    <input type="radio" name="gender" value="女性" {{ old('gender') === '女性' ? 'checked' : '' }} required>
                    <span>女性</span>
                </label>
            </div>
        </div>
        <div>
            <label for="message">お問い合わせ内容</label>
            <textarea id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
        </div>
        <div>
            <button type="submit">確認画面へ</button>
        </div>
    </form>
</main>
@endsection

@section('script_body')
@endsection
