<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class XacThucEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('[CineHome] Xác thực địa chỉ email của bạn')
            ->view('emails.xac_thuc_email', [
                'url' => $url,
                'user' => $notifiable,
            ]);
    }
}
