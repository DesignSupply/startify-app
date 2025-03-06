<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <p>{{ $name }} 様</p>
        <p>
            お問い合わせありがとうございます。<br>
            以下の内容でお問い合わせを受け付けました。
        </p>
        <p>【お名前】<br>{{ $name }}</p>

        @if(!empty($company))
            <p>【会社名】<br>{{ $company }}</p>
        @endif

        <p> 【メールアドレス】</strong><br>{{ $email }}</p>

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
        <p>
            内容を確認の上、担当者より折り返しご連絡させていただきます。<br>
            なお、お問い合わせの内容によっては、回答までにお時間をいただく場合がございます。<br>
            あらかじめご了承ください。
        </p>
        <p>
            ※このメールは自動送信されています。<br>
            ※このメールに心当たりがない場合は、このメールを破棄してください。
        </p>
    </body>
</html>
