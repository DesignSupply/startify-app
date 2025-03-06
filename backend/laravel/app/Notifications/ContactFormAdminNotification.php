<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ContactFormAdminNotification extends Notification
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
            ->subject('ウェブサイトからお問い合わせがありました')
            ->view('emails.contact-notification', [
                'name' => $this->data['name'],
                'company' => $this->data['company'] ?? '',
                'email' => $this->data['email'],
                'phone' => $this->data['phone'] ?? '',
                'url' => $this->data['url'] ?? '',
                'inquiry_type' => $this->data['inquiry_type'] ?? [],
                'gender' => $this->data['gender'],
                'inquiry_message' => $this->data['message'],
                'ip_address' => $this->data['ip_address'],
                'user_agent' => $this->data['user_agent'],
                'submitted_at' => Carbon::now()->format('Y年m月d日 H:i:s'),
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
