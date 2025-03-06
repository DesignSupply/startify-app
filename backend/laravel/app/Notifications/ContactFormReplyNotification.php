<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class ContactFormReplyNotification extends Notification
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('お問い合わせありがとうございます')
            ->view('emails.contact-reply', [
                'name' => $this->data['name'],
                'company' => $this->data['company'] ?? '',
                'email' => $this->data['email'],
                'phone' => $this->data['phone'] ?? '',
                'url' => $this->data['url'] ?? '',
                'inquiry_type' => $this->data['inquiry_type'] ?? [],
                'gender' => $this->data['gender'],
                'inquiry_message' => $this->data['message'],
            ]);
    }

    /**
     * フォームデータをデータベースに保存するときに使用
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
