<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminPasswordResetNotification extends Notification
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
        $url = url(route('admin.password-reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]));

        return (new MailMessage)
            ->subject('【'.config('app.name').'】 管理者パスワードリセットのお知らせ')
            ->view('emails.admin-password-reset', [
                'reset_url' => $url,
                'user_name' => $notifiable->name,
            ]);
    }
}
