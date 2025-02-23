<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SignUpNotification extends Notification
{
    use Queueable;

    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('signup.verify', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]));

        return (new MailMessage)
            ->subject('【'.config('app.name').'】 新規ユーザー登録のお知らせ')
            ->view('emails.signup-verify', [
                'url' => $url,
                'email' => $notifiable->email,
            ]);
    }
}
