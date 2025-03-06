@extends('layouts.default')

@section('title', 'お問い合わせ確認')

@section('meta')
@endsection

@section('style')
@endsection

@section('script_head')
@endsection

@section('content')
<main class="app-main">
    <h1>お問い合わせ確認</h1>
    <form method="POST" action="{{ route('contact.send') }}">

        @csrf

        <div>
            <div>お名前</div>
            <div>{{ $inputs['name'] }}</div>
            <input type="hidden" name="name" value="{{ $inputs['name'] }}">
        </div>
        <div>
            <div>会社名</div>
            <div>{{ $inputs['company'] ?? '' }}</div>
            <input type="hidden" name="company" value="{{ $inputs['company'] ?? '' }}">
        </div>
        <div>
            <div>メールアドレス</div>
            <div>{{ $inputs['email'] }}</div>
            <input type="hidden" name="email" value="{{ $inputs['email'] }}">
        </div>
        <div>
            <div>電話番号</div>
            <div>{{ $inputs['phone'] ?? '' }}</div>
            <input type="hidden" name="phone" value="{{ $inputs['phone'] ?? '' }}">
        </div>
        <div>
            <div>ウェブサイトURL</div>
            <div>{{ $inputs['url'] ?? '' }}</div>
            <input type="hidden" name="url" value="{{ $inputs['url'] ?? '' }}">
        </div>
        <div>
            <div>お問い合わせ種別</div>
            <div>

                @if(isset($inputs['inquiry_type']) && count($inputs['inquiry_type']) > 0)
                    @foreach($inputs['inquiry_type'] as $type)
                        {{ $type }}@if(!$loop->last)、@endif
                    @endforeach
                @else
                @endif

            </div>

            @if(isset($inputs['inquiry_type']))
                @foreach($inputs['inquiry_type'] as $type)
                    <input type="hidden" name="inquiry_type[]" value="{{ $type }}">
                @endforeach
            @endif

        </div>
        <div>
            <div>性別</div>
            <div>
                {{ $inputs['gender'] }}
            </div>
            <input type="hidden" name="gender" value="{{ $inputs['gender'] }}">
        </div>
        <div>
            <div>お問い合わせ内容</div>
            <div>{{ $inputs['message'] }}</div>
            <input type="hidden" name="message" value="{{ $inputs['message'] }}">
        </div>
        <div>
            <button type="button" onclick="history.back()">修正する</button>
            <button type="submit">送信する</button>
        </div>
    </form>
</main>
@endsection

@section('script_body')
@endsection
