<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <p>{{ $user_name }} 様</p>
        <p>
            管理者パスワードリセットのリクエストを受け付けました。<br>
            以下のリンクをクリックして、パスワードの再設定を行ってください
        </p>
        <p>
            <a href="{{ $reset_url }}">パスワードを再設定する</a>
        </p>
        <p>
            ※このリンクの有効期限は60分です。<br>
            ※このメールに心当たりがない場合は、このメールを破棄してください。
        </p>
    </body>
</html>
