<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <p>ウェブサイト管理者 様</p>
        <p>
            ウェブサイトからお問い合わせがありました。<br>
            以下の内容をご確認ください。
        </p>
        <p>【お名前】<br>{{ $name }}</p>

        @if(!empty($company))
            <p>【会社名】<br>{{ $company }}</p>
        @endif

        <p>【メールアドレス】<br>{{ $email }}</p>

        @if(!empty($phone))
            <p>【電話番号】<br>{{ $phone }}</p>
        @endif

        @if(!empty($url))
            <p>【ウェブサイトURL】<br>{{ $url }}</p>
        @endif

        @if(!empty($inquiry_type) && is_array($inquiry_type))
            <p>【お問い合わせ種別】<br>{{ implode('、', $inquiry_type) }}</p>
        @endif

        <p>【性別】<br>{{ $gender }}</p>
        <p>【お問い合わせ内容】<br>{!! nl2br(e($inquiry_message)) !!}</p>
        <p>【IPアドレス】<br>{{ $ip_address }}</p>
        <p>【ユーザーエージェント】<br>{{ $user_agent }}</p>
        <p>【送信日時】<br>{{ $submitted_at }}</p>
        <p>
            このメールは自動送信されています。<br>
            お問い合わせへの対応をお願いいたします。
        </p>
    </body>
</html>
