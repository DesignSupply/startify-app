<?php

return [

    // CORS を適用するパス
    'paths' => [
        'api/*',
    ],

    // 許可するオリジン（カンマ区切りのENVを配列に）
    'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')))),

    // オリジンのパターン（未使用）
    'allowed_origins_patterns' => [],

    // 許可するメソッド
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // 許可するヘッダー
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept', 'Origin'],

    // ブラウザへ公開するヘッダー
    'exposed_headers' => [],

    // プリフライトのキャッシュ秒数
    'max_age' => 0,

    // 認証情報（Cookie等）を許可
    'supports_credentials' => true,
];


